# Ejercicio 3

## Objetivo

Implementar una aplicación PHP que muestre un mensaje de una base de datos MySQL junto con el ID del Pod que atiende la solicitud. La implementación contará con 3 réplicas de PHP para alta disponibilidad y una base de datos MySQL persistente.

## Requisitos previos

- kubectl herramienta de línea de comandos instalada y configurada
- Acceso a un cluster de Kubernetes (Minikube, Kind, proveedor de nube, etc.)
- Docker instalado localmente (Docker Desktop o Docker Engine)
- Un editor de texto para código y archivos YAML

## Paso 1: Crear un namespace

Aísla los recursos del ejercicio dentro de un namespace dedicado.

```bash
eval $(minikube docker-env)
minikube start --driver=docker
kubectl create namespace guestbook-ha
```

## Paso 2: Preparar la aplicación PHP y la imagen de Docker

### a. index.php

Crear un archivo llamado index.php. Esta versión se conecta a la base de datos, recupera el mensaje y también muestra el nombre de host del Pod (que Kubernetes usa como ID del Pod).

```php
<!DOCTYPE html>
<html>
<head>
   <title>HA Guestbook</title>
   <style>
       body { font-family: sans-serif; }
       .container { margin: 20px; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
       .pod-id { font-size: 0.8em; color: #666; margin-top: 15px; }
   </style>
</head>
<body>
   <div class="container">
       <h1>
           <?php

           $db_host = getenv('DB_HOST') ?: 'mysql-service';
           $db_user = getenv('DB_USER');
           $db_pass = getenv('DB_PASSWORD');
           $db_name = getenv('DB_NAME') ?: 'guestbook';

           $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

           $message_from_db = "Error connecting to DB.";
           if ($conn->connect_error) {
               $message_from_db = "Database Connection Failed: " . htmlspecialchars($conn->connect_error);
           } else {
               $sql = "SELECT message FROM welcome_message WHERE id = 1";
               $result = $conn->query($sql);

               if ($result && $result->num_rows > 0) {
                   $row = $result->fetch_assoc();
                   $message_from_db = htmlspecialchars($row['message']);
               } elseif ($result) {
                   $message_from_db = "(No message found with ID 1)";
               } else {
                   $message_from_db = "Query Error: " . htmlspecialchars($conn->error);
               }
               $conn->close();
           }

           echo "Message: " . $message_from_db;
           ?>
       </h1>
       <div class="pod-id">
           Served by Pod: <?php echo htmlspecialchars(gethostname()); ?>
       </div>

   </div>
</body>
</html>
```

### b. Dockerfile

Crear un archivo llamado Dockerfile en el mismo directorio que index.php.

```dockerfile
FROM php:8.1-apache

RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install mysqli && docker-php-ext-enable mysqli

COPY index.php /var/www/html/
RUN chmod 644 /var/www/html/index.php
```

### c. Crear y cargar una imagen de Docker

Dentro del directorio que contiene index.php y Dockerfile.

```bash
docker build -t php-guestbook-ha:v1 .
minikube image load php-guestbook-ha:v1
```

## Paso 3: Implementar una base de datos con estado (MySQL)
### a. Crear el SECRET de la base de datos
Reemplazar ENC[user], ENC[passw] y ENC[rootpassw] por valores cifrados en base64.

```bash
echo -n “your_value” | base64
```

Crear mysql-credentials.yaml:

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: mysql-credentials
type: Opaque
data:
  user: ENC[user] # Base64 encoded username
  password: ENC[passw] # Base64 encoded password
  rootpassword: ENC[rootpassw] # Base64 encoded rootpassword
```

Aplicar:

```bash
kubectl apply -f mysql-credentials.yaml -n guestbook-ha
```

### b. Definir PersistentVolumeClaim (PVC)

Crear mysql-pvc.yaml:

```yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: mysql-data-pvc
  namespace: guestbook-ha
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
```

Aplicar:

```bash
kubectl apply -f mysql-pvc.yaml -n guestbook-ha
```

### c. Definir servicio headless para StatefulSet

Crear mysql-headless-svc.yaml:

```yaml
apiVersion: v1
kind: Service
metadata:
  name: mysql-db-headless
  namespace: guestbook-ha
spec:
  clusterIP: None # Headless service
  selector:
    app: mysql-db # Selects the MySQL pod
  ports:
    - port: 3306
      targetPort: 3306
```

Aplicar:

```bash
kubectl apply -f mysql-headless-svc.yaml -n guestbook-ha
```

### d. Definir el servicio ClusterIP para la aplicación

Crear mysql-svc.yaml:

```yaml
apiVersion: v1
kind: Service
metadata:
  name: mysql-db-service # Stable DNS name PHP app will use
  namespace: guestbook-ha
spec:
  selector:
    app: mysql-db # Selects the MySQL pod
  ports:
    - port: 3306
      targetPort: 3306
```

Aplicar:

```bash
kubectl apply -f mysql-svc.yaml -n guestbook-ha
```

### e. Definir MySQL StatefulSet

> [!NOTE]  
> No descomentar storageClassName.

Crear mysql-statefulset.yaml:

```yaml
apiVersion: apps/v1
kind: StatefulSet
metadata:
  name: mysql-db
  namespace: guestbook-ha
spec:
  serviceName: mysql-db-headless # Links to the headless service
  replicas: 1
  selector:
    matchLabels:
      app: mysql-db
  template:
    metadata:
      labels:
        app: mysql-db
    spec:
      terminationGracePeriodSeconds: 10
      containers:
        - name: mysql
          image: mysql:8.0
          ports:
            - containerPort: 3306
              name: mysql
          env:
            - name: MYSQL_ROOT_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-credentials
                  key: rootpassword
            - name: MYSQL_DATABASE
              value: guestbook # DB name defined here
            - name: MYSQL_USER
              valueFrom:
                secretKeyRef:
                  name: mysql-credentials
                  key: user
            - name: MYSQL_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-credentials
                  key: password
          volumeMounts:
            - name: mysql-persistent-storage
              mountPath: /var/lib/mysql
          # --- Best Practice: Add Liveness and Readiness Probes ---
          # readinessProbe:
          #   exec:
          #     command: ["mysqladmin", "ping", "-h", "127.0.0.1", "--silent"]
          #   initialDelaySeconds: 10
          #   periodSeconds: 5
          #   timeoutSeconds: 2
          # livenessProbe:
          #   exec:
          #     command: ["mysqladmin", "ping", "-h", "127.0.0.1", "--silent"]
          #   initialDelaySeconds: 30
          #   periodSeconds: 10
          #   timeoutSeconds: 5
  volumeClaimTemplates: # Template for the PVC
    - metadata:
        name: mysql-persistent-storage
      spec:
        accessModes: ["ReadWriteOnce"]
        resources:
          requests:
            storage: 1Gi
        # storageClassName: <your-storage-class> # Must match PVC if specified there
```

Aplicar:

```bash
kubectl apply -f mysql-statefulset.yaml -n guestbook-ha
```

### f. Crear esquema de base de datos y datos

- Esperar a que el Pod mysql-db-0 se esté ejecutándo (STATUS=Running): `kubectl get pods -n guestbook-ha -l app=mysql-db`

Guardar el nombre del pod de MySQL en una variable para facilitar el uso:

```bash
MYSQL_POD=$(kubectl get pods -n guestbook-ha -l app=mysql-db -o jsonpath='{.items[0].metadata.name}')
```

Ingresar al pod:

- El password debe ir pegado a la “p”. Debe tener el mismo valor que el rootpassword del archivo mysql-credentials.yaml pero sin cifrar.
- Ej: -pnube

```bash
kubectl exec -it $MYSQL_POD -n guestbook-ha -- mysql -u root -pYOUR_ROOT_PASSWORD
```

Ejecutar comandos SQL:

```sql
CREATE DATABASE IF NOT EXISTS guestbook;
USE guestbook;

CREATE TABLE IF NOT EXISTS welcome_message (
id INT PRIMARY KEY,
message VARCHAR(255) NOT NULL
);

INSERT INTO welcome_message (id, message) VALUES (1, 'Hello from Persistent MySQL!')
ON DUPLICATE KEY UPDATE message='Hello from Persistent MySQL!';

create user 'guestbook_user'@'%'; --user can connect from any host

quit;
```

> [!NOTE]
> Una práctica recomendada para producción es utilizar un initContainer en StatefulSet para ejecutar este SQL automáticamente.

## Paso 4: Implementar una aplicación web de alta disponibilidad (PHP)

### a. Definir la implementación de PHP

Crear php-deployment.yaml:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: php-guestbook-web
  namespace: guestbook-ha
spec:
  replicas: 3 # --- > HA Requirement: 3 replicas
  selector:
    matchLabels:
      app: php-guestbook
  template:
    metadata:
      labels:
        app: php-guestbook
    spec:
      containers:
        - name: php-guestbook
          image: php-guestbook-ha:v1 # Use the image you built
          imagePullPolicy: Never
          ports:
            - containerPort: 80
          env:
            - name: DB_HOST
              value: "mysql-db-service" # Connect via ClusterIP service name
            - name: DB_USER
              valueFrom:
                secretKeyRef:
                  name: mysql-credentials
                  key: user
            - name: DB_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-credentials
                  key: password
            - name: DB_NAME
              value: guestbook
          # --- Best Practice: Add Liveness and Readiness Probes ---
          readinessProbe:
            httpGet:
              path: /index.php # Check if the app can serve requests
              port: 80
            initialDelaySeconds: 5
            periodSeconds: 10
            timeoutSeconds: 2
            failureThreshold: 3
          livenessProbe:
            httpGet:
              path: /index.php # Basic check if the server is running
              port: 80
            initialDelaySeconds: 15
            periodSeconds: 20
            timeoutSeconds: 5
```

Aplicar:

```bash
kubectl apply -f php-deployment.yaml -n guestbook-ha
```

### b. Definir el servicio PHP

> [!NOTE]  
> Ser recomienda usar LoadBalancer para proveedores de nube y NodePort para clústeres locales como Minikube/Kind.
> Minikube no debería tener problemas con LoadBalancer si queremos hacer uso del mismo en el clúster local.
> https://stackoverflow.com/questions/34443138/difference-between-nodeport-and-loadbalancer

Crear php-service.yaml:

```yaml
apiVersion: v1
kind: Service
metadata:
  name: php-guestbook-service
  namespace: guestbook-ha
spec:
  type: LoadBalancer # Change to NodePort if needed
  selector:
    app: php-guestbook # Selects pods managed by the Deployment
  ports:
    - protocol: TCP
      port: 80 # Port accessible externally (or on the node for NodePort)
      targetPort: 80 # Port the container listens on
      # nodePort: 30080 # Specify for NodePort type, if desired
```

Aplicar:

```bash
kubectl apply -f php-service.yaml -n guestbook-ha
```

## Paso 5: Realizar la verificación

### a. Consultar recursos

Verificar que todos los pods, servicios y PVC estén ejecutándose/vinculados.

```bash
# Deberíamos ver 1 pod mysql-db, 3 pods php-guestbook, los servicios y un PVC.
kubectl get pods,svc,pvc -n guestbook-ha
```

### b. Solicitar acceso

- Buscar la IP externa (LoadBalancer) o la IP/puerto del nodo (NodePort).

LoadBalancer:

```bash
kubectl get service php-guestbook-service -n guestbook-ha -o jsonpath='{.status.loadBalancer.ingress[0].ip}'
# Puede que la IP tarde un minuto en asignarse.
# Acceso: http://<IP_EXTERNA>
```

NodePort:

```bash
kubectl get svc php-guestbook-service -n guestbook-ha
# Podemos verificar el puerto dentro del archivo yaml del servicio, si lo definimos ahí en un principio.
# Acceso: http://<NODO_IP>:<NODO_PUERTO>
```

Atajo de Minikube (sin importar el tipo de servicio elegido):

```bash
minikube service php-guestbook-service -n guestbook-ha --url
```

### c. Verificar salida

1. Abrir la URL en el navegador.
2. Deberíamos ver: `Message: Hello from Persistent MySQL! Served by Pod: php-guestbook-web-<some-unique-id>`
3. Actualizar la página varias veces con unos segundos entre cada actualización. Deberíamos ver que el mensaje sigue siendo el mismo, pero el ID del Pod cambia a medida que LoadBalancer distribuye las solicitudes entre las 3 réplicas.

## Paso 6: Probar la alta disponibilidad (opcional)

### HA de nivel web

- Eliminar uno de los Pods de PHP. Observar que Kubernetes crea automáticamente un reemplazo.

```bash
kubectl get pods -n guestbook-ha -w # obtener nombre del pod
kubectl delete pod <php-pod-name> -n guestbook-ha # eliminar pod
kubectl get pods -n guestbook-ha -w # verificar que se genere el reemplazo
```

### Persistencia de la base de datos

- Eliminar el Pod de MySQL. Observar el StatefulSet recreándolo con el mismo nombre y adjuntando el mismo PVC. Es probable que la aplicación falle mientras la base de datos esté inactiva, pero debería recuperarse y mostrar el mismo mensaje una vez que la base de datos esté lista, lo que demuestra la persistencia de los datos.

```bash
kubectl get pods -n guestbook-ha -w # obtener nombre del pod
kubectl delete pod mysql-db-0 -n guestbook-ha # eliminar pod
kubectl get pods -n guestbook-ha -w # verificar que se genere el reemplazo
```

## Paso 7: Realizar la limpieza

```bash
minikube dashboard # Revisar los recursos asociados
```

```bash
kubectl delete namespace guestbook-ha
# Eliminar todos los recursos asociados eliminando el espacio de nombres.
```

```bash
# Eliminar imagen de docker generada
docker image ls
docker image rm <repository:tag>
```

```bash
minikube stop
```

> [!NOTE]  
> Ingresar a minikube dashboard para verificar si se eliminaron todos los recursos asociados.
