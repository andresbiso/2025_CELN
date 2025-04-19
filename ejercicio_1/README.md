# Ejercicio 1

1. Instalar node y npm

- https://nodejs.org/en/download
- Options: v22.14.0 (LTS); Linux; nvm; npm

```bash
# Download and install nvm:
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.2/install.sh | bash

# in lieu of restarting the shell
\. "$HOME/.nvm/nvm.sh"

# Download and install Node.js:
nvm install 22

# Verify the Node.js version:
node -v # Should print "v22.14.0".
nvm current # Should print "v22.14.0".

# Verify npm version:
npm -v # Should print "10.9.2".
```

2. Crear un archivo index.js

```javascript
const express = require("express");
const app = express();
const port = 3000;

app.get("/", (req, res) => {
  res.send(`<!DOCTYPE html>
    <html>
      <head>
        <title>Hello, World!</title>
      </head>
      <body>
        <h1>Hello, World!</h1>
        <p>Welcome to this simple web application!</p>
      </body>
    </html>`);
});

app.listen(port, () => {
  console.log(`Server running on port ${port}`);
});
```

3. Dentro de la carpeta del proyecto: `npm init`.
4. `npm install express`
5. Crear un Dockerfile

```dockerfile
FROM node:18

WORKDIR /app

COPY package\*.json ./
COPY index.js ./

RUN npm install

EXPOSE 3000

CMD ["node", "index.js"]
```

6. Crear un archivo de deployment

```bash
kubectl create deployment myapp --image=myapp:v1 --dry-run=client -o yaml > myapp-deployment.yaml
```

7. Abrir el myapp-deployment.yaml

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  creationTimestamp: null
  labels:
    app: myapp
  name: myapp
spec:
  replicas: 1
  selector:
    matchLabels:
      app: myapp
  strategy: {}
  template:
    metadata:
      creationTimestamp: null
      labels:
        app: myapp
    spec:
      containers:
        - image: myapp:v1
          name: myapp
          resources: {}
          imagePullPolicy: Never # Agregar esta línea
          ports: # Agregar esta linea
            - containerPort: 3000 # Agregar esta linea
status: {}
```

8. Levantar minikube

```bash
eval $(minikube docker-env)
minikube start --driver=docker
```

9. Construir la imagen de Docker

```bash
docker build -t myapp:v1 .
```

10. Aplicar el deployment

```bash
kubectl apply -f myapp-deployment.yaml
```

11. Verificar que funcione ejecutando

```bash
kubectl get pods -o wide
```

- En caso de verificar que el status del pod es ErrImageNeverPull: revisar myapp-deployment.yaml, ejecutar nuevamente el comando “eval…” y realizar un rebuild de docker.

12. Exponer la aplicación a través de un servicio

```bash
kubectl expose deployment myapp --type=NodePort --port=3000
```

13. Testear el deployment

Browser:

```bash
minikube service myapp --url
```

Terminal:

```bash
curl <url>
```

14. Cleanup de recursos

```bash
minikube dashboard # Revisar los recursos asociados
```

```bash
kubectl get deployments # Revisar los deployments
kubectl delete deployment <deployment_name>
```

```bash
kubectl get services # Revisar los services
kubectl delete service <service_name>
```

```bash
kubectl get configmaps # Revisar los config maps
kubectl delete configmap <configmap_name>
```

```bash
kubectl get pvc # Revisar los persistent volume claims
kubectl delete pvc <pvc_name>
```

```bash
kubectl get secrets # Revisar los secrets
kubectl delete secret <secret_name>
```

```bash
kubectl get pv # Revisar los persistent volumes
kubectl delete pv <pv_name>
```

```bash
# Eliminar imagen de docker generada
docker image ls
docker image rm <repository:tag>
```

```bash
minikube stop
```

- Más información: https://www.educative.io/blog/kubernetes-deployments-pods-services
