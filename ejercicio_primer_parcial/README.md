# Ejercicio - Primer Parcial

## Enunciado

La empresa "Innovate Solutions" necesita desplegar urgentemente la primera versión de su nueva plataforma de e-commerce en un entorno de desarrollo basado en Kubernetes (Minikube, preferiblemente configurado con múltiples nodos). Se les encarga a ustedes, como equipo de DevOps, implementar la arquitectura completa de tres capas: un Frontend web estático (HTML/PHP servido por Nginx), una API Backend (Node.js/Express) y una base de datos (PostgreSQL). Deben utilizar Deployments con múltiples réplicas (al menos 4) para el frontend y backend, y un StatefulSet para la base de datos asegurando la persistencia de datos mediante PersistentVolumeClaims.

Es requisito indispensable para "Innovate Solutions" garantizar la resiliencia y seguir las mejores prácticas desde esta fase inicial. Por ello, deberán gestionar la configuración de la API y las credenciales de la base de datos de forma segura utilizando ConfigMaps y Secrets respectivamente. Además, implementarán livenessProbes y readinessProbes para todos los componentes, definirán requests y limits de CPU/Memoria para cada contenedor, y configurarán topologySpreadConstraints en los Deployments para simular una distribución tolerante a fallos entre diferentes "zonas" (nodos de Minikube).

## Objetivo

El objetivo es entregar un sistema funcional, observable y preparado para futuras implementaciones (guía con los pasos realizados).

## Estructura de la aplicación

1. Interfaz (HTML/PHP): Una página HTML con PHP estática simple para recuperar datos de la API Backend. Servido por Nginx en una implementación con múltiples réplicas.
2. Server (Node.js/Express): Una API simple que se conecta a la base de datos PostgreSQL para recuperar/almacenar datos. Se ejecuta en una implementación con múltiples réplicas.
3. Base de datos (PostgreSQL): Almacena datos de la aplicación. Se ejecuta como StatefulSet con PersistentVolumeClaim.

## Conceptos

A continuación, paso a dejar una breve explicación de cada uno de los conceptos utilizados en el desarrollo de la guía:

**Docker:** Plataforma que permite empaquetar aplicaciones y sus dependencias en unidades aisladas llamadas contenedores. Facilita el despliegue, escalabilidad y portabilidad entre distintos entornos.

**Kubernetes:** Sistema de orquestación que automatiza la gestión, escalado y despliegue de aplicaciones en contenedores. Proporciona herramientas para asegurar disponibilidad, distribución eficiente de recursos y resiliencia en entornos de producción.
Este sistema hace uso principalmente de clústeres, nodos, pods y contenedores.

> [!Note]
>
> - Clúster: Es un nodo o un conjunto de múltiples nodos compuestos por pods que trabajan juntos para ejecutar aplicaciones en contenedores.
> - Nodos: Es una instancia de una unidad de cómputo dentro del clúster. Están compuestos por pods.
> - Pods: La unidad mínima de Kubernetes. Cada Pod puede contener uno o varios contenedores que comparten recursos y red.
> - Contenedores: Son unidades independientes que contienen una aplicación y sus dependencias. Son gestionados dentro de Pods.

**Minikube:** Herramienta para ejecutar un clúster de Kubernetes en una máquina local. Se puede configurar con uno o con múltiples nodos para simular un entorno más realista.
Simplifica el proceso de aprendizaje y experimentación con Kubernetes al proporcionar un entorno manejable sin las complejidades de un clúster a gran escala.

**Deployments:** Controlan el despliegue y gestión de aplicaciones en Kubernetes, permitiendo actualizaciones y escalamiento automático. Hacemos uso de estos para el frontend y backend con múltiples réplicas.

**StatefulSet:** Tipo de controlador que gestiona aplicaciones con estado, asegurando que cada instancia tenga una identidad única y persistencia de datos, como en el caso de la base de datos PostgreSQL.

**PersistentVolumeClaims (PVC):** Mecanismo para solicitar almacenamiento persistente en Kubernetes, garantizando que los datos de la base de datos no se pierdan tras reinicios o escalamiento.
Kubernetes asigna un PV que cumpla con las características solicitadas en el PVC, asegurando que la aplicación reciba almacenamiento persistente.

**PersistentVolume (PV):** Es el recurso de almacenamiento físico dentro del clúster. Puede estar respaldado por discos locales, NFS, almacenamiento en la nube, etc.

**ConfigMaps:** Recurso para gestionar la configuración de aplicaciones de manera externa, evitando la necesidad de modificar contenedores directamente.

**Secrets:** Recurso diseñados para manejar información sensible como credenciales y contraseñas, protegiendo los datos de la base de datos.

**LivenessProbes:** Comprobaciones automáticas para verificar que un contenedor sigue funcionando correctamente. Si falla, Kubernetes reinicia el contenedor.

**ReadinessProbes:** Indican cuándo un contenedor está listo para recibir tráfico, evitando que los servicios envíen solicitudes a instancias aún no preparadas.

**Requests y Limits de CPU/Memoria:** Controlan la asignación de recursos en Kubernetes, evitando que un contenedor consuma más recursos de los permitidos y asegurando una distribución eficiente.

**TopologySpreadConstraints:** Reglas para distribuir los pods en distintos nodos del clúster, mejorando la tolerancia a fallos y la resiliencia de la aplicación.

**Service:** Es un recurso en Kubernetes que permite exponer aplicaciones dentro del clúster y gestionar la comunicación entre pods. Actúa como un punto de acceso estable, distribuyendo tráfico hacia las réplicas de un Deployment o StatefulSet según la configuración.

**Headless Service:** Variante de un Service que no asigna una dirección IP estable. En lugar de eso, permite que las aplicaciones accedan directamente a los pods individuales, suele ser utilizado con bases de datos y servicios que requieren conocimiento de cada instancia en el clúster.

## Pasos Previos

> [!IMPORTANT]
>
> - Esta guía supone se cuenta con un dispositivo ejecutando el sistema operativo Ubuntu.
> - Se supone de que se cuenta con un equipo con las especificaciones necesarias para poder realizar la instalación del etorno.
> - Se recomienda un mínimo de unos 40 GB de almacenamiento disponible, contar con al menos una CPU de 2 cores de una generación reciente, contar con al menos 4 GiB de memoria RAM disponible y que el sistema operativo instalado pueda hacer uso de docker.

1. Se debe contar con node y npm instalados antes de continuar.

- https://nodejs.org/en/download
- Opciones recomendadas: v22.14.0 (LTS) para Linux usando nvm y npm.

```bash
# Descargar e instalar nvm:
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.2/install.sh | bash

# Cargar nvm en la sesión actual sin reiniciar la terminal
\. "$HOME/.nvm/nvm.sh"

# Descargar e instalar Node.js:
nvm install 22

# Verificar versión de Node.js:
node -v
nvm current

# Verifricar versión de npm:
npm -v
```

2. Crear carpetas del proyecto.

```bash
mkdir ecommerce-app    # Carpeta raíz del proyecto
cd ecommerce-app
mkdir backend          # Código del backend (API)
mkdir frontend         # Código del frontend
mkdir k8s              # Archivos de configuración de Kubernetes
```

3. Instalar docker: https://docs.docker.com/engine/install/ubuntu/
4. Instalar kubectl: https://kubernetes.io/docs/tasks/tools/install-kubectl-linux/#install-using-native-package-management
5. Instalar minikube: https://minikube.sigs.k8s.io/docs/start/?arch=%2Flinux%2Fx86-64%2Fstable%2Fdebian+package
6. En una terminal ejecutar: `sudo usermod -aG docker $USER && newgrp docker`

> [!NOTE]
> Al finalizar estos pasos, tendremos un entorno local que puede hacer uso de esta guía para la configuración del sistema de ecommerce.

## Paso 1 - Ambiente local con minikube

### a. Levantar minikube con configuración inicial

```bash
eval $(minikube docker-env --profile=multinode-cluster)
minikube start --driver=docker --nodes=3 --profile=multinode-cluster
```

### b. Configurar el espacio de nombres

```bash
kubectl create namespace ecommerce-app
```

## Paso 2 - Base de Datos

### Secret de la base de datos

- Reemplazar los comodines con valores codificados en Base64
- `echo -n 'value' | base64`

Crear k8s/postgres-secret.yaml:

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: postgres-secret
  namespace: ecommerce-app
type: Opaque
data:
  DB_USER: dXNlcg== # Base64 for 'user'
  DB_PASSWORD: <your_base64_password> # e.g., cGFzc3dvcmQ= for 'password'
```

Aplicar:

```bash
kubectl apply -f postgres-secret.yaml -n ecommerce-app
```

### Configmap de la base de datos

Crear k8s/postgres-configmap.yaml:

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: postgres-config
  namespace: ecommerce-app
data:
  DB_HOST: postgres-service # Kubernetes Service name for PostgreSQL
  DB_NAME: ecommerce_db # Database name
  DB_PORT: "5432" # Default PostgreSQL port
```

Aplicar:

```bash
kubectl apply -f postgres-configmap.yaml -n ecommerce-app
```

### Headless Service

Crear k8s/postgres-headless-service.yaml:

```bash
# Headless Service for StatefulSet DNS
apiVersion: v1
kind: Service
metadata:
  name: postgres-db-headless
  namespace: ecommerce-app
spec:
  clusterIP: None
  selector:
    app: postgres-db
  ports:
    - port: 5432
      targetPort: 5432
```

Aplicar:

```bash
kubectl apply -f postgres-headless-service.yaml -n ecommerce-app
```

### ClusterIp Service

Crear k8s/postgres-db-service.yaml:

```bash
# ClusterIP Service for Backend API access
apiVersion: v1
kind: Service
metadata:
  name: postgres-db-service
  namespace: ecommerce-app
spec:
  selector:
    app: postgres-db
  ports:
    - protocol: TCP
      port: 5432
      targetPort: 5432
```

Aplicar:

```bash
kubectl apply -f postgres-db-service.yaml -n ecommerce-app
```

### Statefulset base de datos

Crear k8s/postgres-statefulset.yaml:

```yaml
apiVersion: apps/v1
kind: StatefulSet
metadata:
  name: postgres-db
  namespace: ecommerce-app
spec:
  serviceName: postgres-db-headless
  replicas: 1 # Single replica for simplicity in Minikube HA
  selector:
    matchLabels:
      app: postgres-db
  template:
    metadata:
      labels:
        app: postgres-db
    spec:
      terminationGracePeriodSeconds: 10
      containers:
        - name: postgres
          image: postgres:15
          ports:
            - containerPort: 5432
              name: postgres
          env:
            - name: POSTGRES_DB
              valueFrom:
                configMapKeyRef:
                  name: postgres-config
                  key: DB_NAME # Use existing ConfigMap key
            - name: POSTGRES_USER
              valueFrom:
                secretKeyRef:
                  name: postgres-secret
                  key: DB_USER
            - name: POSTGRES_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: postgres-secret
                  key: DB_PASSWORD
            - name: POSTGRES_HOST
              valueFrom:
                configMapKeyRef:
                  name: postgres-config
                  key: DB_HOST
            - name: POSTGRES_PORT
              valueFrom:
                configMapKeyRef:
                  name: postgres-config
                  key: DB_PORT
          volumeMounts:
            - name: postgres-data
              mountPath: /var/lib/postgresql/data
          resources:
            requests:
              cpu: "100m"
              memory: "256Mi"
            limits:
              cpu: "500m"
              memory: "512Mi"
          # Add Liveness/Readiness Probes (e.g., using pg_isready)
          readinessProbe:
            exec:
              command:
                [
                  "pg_isready",
                  "-U",
                  "$(POSTGRES_USER)",
                  "-d",
                  "$(POSTGRES_DB)",
                  "-h",
                  "$(POSTGRES_HOST)",
                  "-p",
                  "$(POSTGRES_PORT)",
                ]
            initialDelaySeconds: 15
            periodSeconds: 10
            timeoutSeconds: 5
          livenessProbe:
            exec:
              command:
                [
                  "pg_isready",
                  "-U",
                  "$(POSTGRES_USER)",
                  "-d",
                  "$(POSTGRES_DB)",
                  "-h",
                  "$(POSTGRES_HOST)",
                  "-p",
                  "$(POSTGRES_PORT)",
                ]
            initialDelaySeconds: 30
            periodSeconds: 15
            timeoutSeconds: 5
  volumeClaimTemplates:
    - metadata:
        name: postgres-data
      spec:
        accessModes: ["ReadWriteOnce"]
        resources:
          requests:
            storage: 1Gi # Request 1 GB persistent storage
        #storageClassName: standard # Specify if needed for your Minikube storage provisioner
```

Aplicar:

```bash
kubectl apply -f postgres-statefulset.yaml -n ecommerce-app
```

### Inicializar base de datos

- Esperar a que el pod postgres-db-0 se esté ejecutando (STATUS=Running).

```bash
kubectl get pods -n ecommerce-app -l app=postgres-db
```

- Ingresar al pod y crear las tablas necesarias para la API de backend.

```bash
kubectl exec -it postgres-db-0 -n ecommerce-app -- psql -U user -d ecommerce_db
```

Inside psql:

```sql
CREATE TABLE IF NOT EXISTS inventory (
    item_id SERIAL PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO inventory (item_name, description, price, quantity, category) VALUES
('Smartphone', 'Latest model with 128GB storage', 699.99, 20, 'Electronics'),
('Sneakers', 'Comfortable running shoes', 89.99, 50, 'Footwear'),
('Backpack', 'Durable travel backpack with multiple compartments', 59.99, 30, 'Accessories'),
('Apples', 'Fresh red apples', 2.99, 50, 'Fruits'),
('Bananas', 'Organic bananas', 1.49, 30, 'Fruits'),
('Oranges', 'Juicy oranges', 3.49, 40, 'Fruits');
\q
```

## Paso 3 - Aplicación Backend

### Generar Backend

Crear backend/package.json:

```json
{
  "name": "backend-api",
  "version": "1.0.0",
  "description": "Simple API for ecommerce",
  "main": "server.js",
  "scripts": {
    "start": "node server.js"
  },
  "dependencies": {
    "express": "^5.1.0",
    "pg": "^8.14.1"
  }
}
```

Crear backend/server.js:

```javascript
const express = require("express");
const { Pool } = require("pg");
const app = express();
const port = process.env.SERVER_PORT || 3001; // Use environment variable or default
const apiHost = process.env.API_HOST || "0.0.0.0"; // Default to all available network interfaces

// PostgreSQL connection pool using environment variables set via ConfigMaps and Secrets
const pool = new Pool({
  user: process.env.DB_USER, // Expected to be set in a Secret
  host: process.env.DB_HOST, // Expected to be set in a ConfigMap
  database: process.env.DB_NAME, // Expected to be set in a ConfigMap
  password: process.env.DB_PASSWORD, // Expected to be set in a Secret
  port: process.env.DB_PORT || 5432, // Default PostgreSQL port
});

// Health check endpoint for Kubernetes probes
app.get("/healthcheck", async (req, res) => {
  try {
    await pool.query("SELECT 1"); // Quick DB check
    res.status(200).send("OK");
  } catch (error) {
    console.error("Database health check failed:", error);
    res.status(500).send("Database unavailable");
  }
});

// API endpoint to get inventory items
app.get("/api/inventory", async (req, res) => {
  try {
    const result = await pool.query(
      "SELECT item_id, item_name, price, quantity FROM inventory ORDER BY item_id"
    );
    res.json(result.rows);
  } catch (err) {
    console.error("Error fetching inventory data:", err);
    res.status(500).send("Error fetching data");
  }
});

// Root response
app.get("/", (req, res) => {
  res.send("Backend API for E-Commerce Inventory is running!");
});

app.listen(port, apiHost, () => {
  console.log(`Backend API listening on port ${port}`);
});
```

Crear backend/Dockerfile:

```dockerfile
FROM node:23-alpine

WORKDIR /app

# Copy dependencies files first
COPY package.json package-lock.json ./

# Install dependencies
RUN npm install --only=production

# Copy application code
COPY server.js .

# Expose the backend service port
EXPOSE 3001

# Set environment variable for API host
ENV API_HOST=0.0.0.0

# Command to run the Node.js app
CMD [ "node", "server.js" ]
```

> [!IMPORTANT]  
> Correr npm install en el directorio de la aplicación backend.
> Esto debería generar el archivo package-lock.json que se utiliza en el Dockerfile.

### Crear y cargar imagen de Docker

En el directorio de la aplicación backend:

```bash
docker build -t backend-api:v1 .
```

```bash
minikube image load backend-api:v1 --profile=multinode-cluster
```

### ConfigMap Backend

Crear k8s/backend-api-configmap.yaml:

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: api-config
  namespace: ecommerce-app
data:
  DB_HOST: postgres-db-service # Service name for the database
  DB_PORT: "5432"
```

Aplicar:

```bash
kubectl apply -f backend-api-configmap.yaml -n ecommerce-app
```

## Service Backend

Crear k8s/bakend-api-service.yaml:

```bash
apiVersion: v1
kind: Service
metadata:
  name: backend-api-service
  namespace: ecommerce-app
spec:
  selector:
    app: backend-api
  ports:
    - protocol: TCP
      port: 3001    # Service port, used by frontend
      targetPort: 3001 # Maps to the Node.js app running inside the container
```

Aplicar:

```bash
kubectl apply -f backend-api-service.yaml -n ecommerce-app
```

## Deployment Backend

Crear k8s/backend-api-deployment.yaml:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: backend-api
  namespace: ecommerce-app
spec:
  replicas: 4
  selector:
    matchLabels:
      app: backend-api
  template:
    metadata:
      labels:
        app: backend-api
    spec:
      containers:
        - name: backend-api
          image: backend-api:v1 # Local image, no registry used
          imagePullPolicy: Never # Ensures Kubernetes doesn't attempt to pull from a registry
          ports:
            - containerPort: 3001
          env:
            # DB Config from ConfigMap
            - name: DB_HOST
              valueFrom:
                configMapKeyRef:
                  name: api-config
                  key: DB_HOST
            - name: DB_PORT
              valueFrom:
                configMapKeyRef:
                  name: api-config
                  key: DB_PORT
            # DB Credentials from Secret
            - name: DB_USER
              valueFrom:
                secretKeyRef:
                  name: postgres-secret
                  key: DB_USER
            - name: DB_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: postgres-secret
                  key: DB_PASSWORD
            # DB Name from PostgreSQL ConfigMap
            - name: DB_NAME
              valueFrom:
                configMapKeyRef:
                  name: postgres-config
                  key: DB_NAME
          resources:
            requests:
              cpu: "50m"
              memory: "128Mi"
            limits:
              cpu: "200m"
              memory: "256Mi"
          readinessProbe:
            httpGet:
              path: /healthcheck
              port: 3001
            initialDelaySeconds: 5
            periodSeconds: 5
          livenessProbe:
            httpGet:
              path: /healthcheck
              port: 3001
            initialDelaySeconds: 15
            periodSeconds: 10
      topologySpreadConstraints:
        - maxSkew: 1
          topologyKey: kubernetes.io/hostname
          whenUnsatisfiable: ScheduleAnyway
          labelSelector:
            matchLabels:
              app: backend-api
```

Aplicar:

```bash
kubectl apply -f backend-api-deployment.yaml -n ecommerce-app
```

- Esperar a que los pods que comienzan con "backend-api-" se estén ejecutando (STATUS=Running).

```bash
kubectl get pods -n ecommerce-app -l app=backend-api
```

## Paso 4 - Aplicación Frontend

### Generar Frontend

Crear frontend/index.php:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Inventory</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        #data-container { margin-top: 20px; border: 1px solid #ccc; padding: 15px; min-height: 50px; background-color: #f9f9f9;}
        #error-container { color: red; margin-top: 10px; font-weight: bold; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Available Inventory Items</h1>

    <div id="data-container">
        <?php
        $apiUrl = getenv('BACKEND_API_URL') . "/api/inventory"; // Fetch from ConfigMap - Kubernetes service URL
        $errorMsg = '';
        $data = null;

        // Create a context for error handling and timeout settings
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
        $responseJson = @file_get_contents($apiUrl, false, $context);

        if ($responseJson === false) {
            $error = error_get_last();
            $errorMsg = "Failed to connect to API: " . ($error['message'] ?? 'Unknown error');
        } else {
            // Check HTTP status code
            if (isset($http_response_header[0]) && strpos($http_response_header[0], '200 OK') === false) {
                $errorMsg = "API Error: Received status " . htmlspecialchars($http_response_header[0]);
            } else {
                $data = json_decode($responseJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errorMsg = "Failed to decode JSON response: " . json_last_error_msg();
                    $data = null;
                }
            }
        }

        // Display data or error message
        if (!empty($errorMsg)) {
            echo '<div id="error-container">' . htmlspecialchars($errorMsg) . '</div>';
        } elseif ($data !== null && count($data) > 0) {
            echo '<ul>';
            foreach ($data as $item) {
                echo '<li>ID: ' . htmlspecialchars($item['item_id']) . ', Name: ' . htmlspecialchars($item['item_name']) . ', Price: $' . htmlspecialchars($item['price']) . ', Quantity: ' . htmlspecialchars($item['quantity']) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No inventory items found.';
        }
        ?>
    </div>

    <p style="font-size: 0.8em; color: #666; margin-top: 15px;">
        Server Software: <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE']); ?>
    </p>
    <p style="font-size: 0.8em; color: #666; margin-top: 5px;">
        Hostname: <?php echo htmlspecialchars(gethostname()); ?>
    </p>
</body>
</html>
```

Crear frontend/nginx.conf:

```nginx
worker_processes auto;
events { worker_connections 1024; }

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    sendfile on;
    keepalive_timeout 65;

    server {
        listen 80 default_server;
        root /var/www/html;
        index index.php index.html;

        location / {
            try_files $uri /index.php$is_args$args;
        }

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass 127.0.0.1:9000;  # Updated to use TCP instead of Unix socket
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_param PATH_INFO $fastcgi_path_info;
        }

        error_page 404 /404.html;
        error_page 500 502 503 504 /50x.html;

        location = /50x.html {
            root /usr/share/nginx/html;
        }
    }
}
```

Crear frontend/supervisord.conf:

```supervisord
[supervisord]
nodaemon=true

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
```

Crear frontend/Dockerfile:

```dockerfile
FROM php:8.1-fpm

# This variant contains PHP's FastCGI Process Manager (FPM)⁠, which is the recommended FastCGI implementation for PHP.
# In order to use this image variant, some kind of reverse proxy (such as NGINX, Apache, or other tool which speaks the FastCGI protocol) will be required.

# Install required dependencies
RUN apt-get update && apt-get install -y libpq-dev nginx supervisor

# Install PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Prepare working directory
WORKDIR /var/www/html
COPY index.php /var/www/html/index.php
RUN chown -R www-data:www-data /var/www/html
RUN chmod 644 /var/www/html/index.php

# Copy configurations
COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Fix permissions
RUN chown -R www-data:www-data /etc/nginx/nginx.conf
RUN mkdir -p /run/php && chown -R www-data:www-data /run/php

# Expose Nginx port
EXPOSE 80

# Start both services using supervisor
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### Construir y cargar imagen de Docker

En el directorio de aplicaciones frontend:

```bash
docker build -t frontend-web:v1 .
```

```bash
minikube image load frontend-web:v1 --profile=multinode-cluster
```

### ConfigMap Frontend

Crear k8s/frontend-configmap.yaml:

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: frontend-config
  namespace: ecommerce-app
data:
  BACKEND_API_URL: "http://backend-api-service.ecommerce-app.svc.cluster.local:3001" # Reference backend API service
```

Aplicar:

```bash
kubectl apply -f frontend-configmap.yaml -n ecommerce-app
```

## Service Frontend

Crear k8s/frontend-service.yaml:

```bash
apiVersion: v1
kind: Service
metadata:
  name: frontend-service
  namespace: ecommerce-app
spec:
  type: NodePort # Expose externally via NodePort for Minikube
  selector:
    app: frontend-web
  ports:
    - protocol: TCP
      port: 80 # Port service listens on
      targetPort: 80 # Port the Nginx container listens on
      nodePort: 30080 # Optional: Specify NodePort (adjust for Minikube)
```

Aplicar:

```bash
kubectl apply -f frontend-service.yaml -n ecommerce-app
```

## Deployment Frontend

Crear k8s/frontend-deployment.yaml:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: frontend-web
  namespace: ecommerce-app
spec:
  replicas: 4 # High availability setup
  selector:
    matchLabels:
      app: frontend-web
  template:
    metadata:
      labels:
        app: frontend-web
    spec:
      containers:
        - name: frontend-web
          image: frontend-web:v1 # Local image, no registry
          imagePullPolicy: Never # Ensures Kubernetes does not pull from a registry
          ports:
            - containerPort: 80 # Nginx default port
          env:
            - name: BACKEND_API_URL
              valueFrom:
                configMapKeyRef:
                  name: frontend-config
                  key: BACKEND_API_URL
          resources:
            requests:
              cpu: "50m"
              memory: "64Mi"
            limits:
              cpu: "100m"
              memory: "128Mi"
          readinessProbe:
            httpGet:
              path: /index.php
              port: 80
            initialDelaySeconds: 5
            periodSeconds: 5
          livenessProbe:
            httpGet:
              path: /index.php
              port: 80
            initialDelaySeconds: 15
            periodSeconds: 10
      topologySpreadConstraints:
        - maxSkew: 1
          topologyKey: kubernetes.io/hostname
          whenUnsatisfiable: ScheduleAnyway
          labelSelector:
            matchLabels:
              app: frontend-web
```

Aplicar:

```bash
kubectl apply -f frontend-deployment.yaml -n ecommerce-app
```

- Esperar a que los pods que comienzan con "frontend-web-" se estén ejecutando (STATUS=Running).

```bash
kubectl get pods -n ecommerce-app -l app=frontend-web
```

## Paso 5 - Verificación

### Check Pods, Servicios, PVC

```bash
kubectl get pods,svc,pvc,statefulset,deployment -n ecommerce-app -o wide
```

- Verificar que todos los pods estén en ejecución, que el PVC esté vinculado y que los servicios tengan IP/puertos.
- Si utiliza varios nodos Minikube, verifique si los pods para frontend-web y backend-api están programados en diferentes nodos debido a topologySpreadConstraints.

### Acceso a la interfaz

```bash
minikube service frontend-service -n ecommerce-app --url --profile=multinode-cluster
```

1. Abrir la URL en el navegador. Debería verse la página HTML.
2. PHP debe llamar a la API de backend (a través del servicio de API de backend), recuperar datos de PostgreSQL y mostrarlos en la página.

### Verificar registros

```bash
kubectl get pods -n ecommerce-app
kubectl logs <pod-name> -n ecommerce-app # Investigar problemas
# Ejemplo: kubectl logs -l app=backend-api -n ecommerce-app --tail=50
# Ejemplo: kubectl logs -l app=frontend-web -n ecommerce-app --tail=50
```

## Paso 6 - Limpieza

```bash
minikube dashboard -p multinode-cluster # Revisar los recursos asociados
```

```bash
kubectl delete namespace ecommerce-app
# Eliminar todos los recursos asociados eliminando el espacio de nombres.
```

```bash
# Eliminar imágenes de docker generadas
docker image ls
docker image rm <repository:tag>
```

```bash
minikube stop -p multinode-cluster
# Detener el clúster Minikube
```

```bash
minikube delete -p multinode-cluster
# Eliminar el clúster Minikube
```

```bash
minikube stop
```

> [!NOTE]  
> Ingresar a minikube dashboard para verificar si se eliminaron todos los recursos asociados.
