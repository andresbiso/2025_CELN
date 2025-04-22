# Ejercicio - Primer Parcial

## Enunciado

La empresa "Innovate Solutions" necesita desplegar urgentemente la primera versión de su nueva plataforma de e-commerce en un entorno de desarrollo basado en Kubernetes (Minikube, preferiblemente configurado con múltiples nodos). Se les encarga a ustedes, como equipo de DevOps, implementar la arquitectura completa de tres capas: un Frontend web estático (HTML/PHP servido por Nginx), una API Backend (Node.js/Express) y una base de datos (PostgreSQL). Deben utilizar Deployments con múltiples réplicas (al menos 4) para el frontend y backend, y un StatefulSet para la base de datos asegurando la persistencia de datos mediante PersistentVolumeClaims.

Es requisito indispensable para "Innovate Solutions" garantizar la resiliencia y seguir las mejores prácticas desde esta fase inicial. Por ello, deberán gestionar la configuración de la API y las credenciales de la base de datos de forma segura utilizando ConfigMaps y Secrets respectivamente. Además, implementarán livenessProbes y readinessProbes para todos los componentes, definirán requests y limits de CPU/Memoria para cada contenedor, y configurarán topologySpreadConstraints en los Deployments para simular una distribución tolerante a fallos entre diferentes "zonas" (nodos de Minikube).

## Objetivo

El objetivo es entregar un sistema funcional, observable y preparado para futuras implementaciones (guía con los pasos realizados).

## Paso 1

## Paso - Generar Backend

1. Instalar node y npm.

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

2. Crear carpeta "backend".

```bash
mkdir backend
```

3. Dentro de la carpeta del proyecto: `npm init`.

4. `npm install express`

Dockerfile, Server.js, Modificar package.json

# Configurar Base de Datos

```sql
CREATE TABLE inventory (
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

Headless Service (postgres-db-headless) for internal Pod-to-Pod communication.

ClusterIP Service (postgres-db-service) for API access.

StatefulSet with persistent storage.

ConfigMap for non-sensitive settings (like database name).

Secret for sensitive credentials (username/password).

🔹 Step 1: Set Up the Project Structure
Before we start deploying, let's organize our codebase properly.

1️⃣ Create the main project directory
sh
mkdir ecommerce-platform
cd ecommerce-platform
2️⃣ Create directories for each component
sh
mkdir backend frontend k8s
backend → Contains the Node.js API.

frontend → Contains the PHP frontend.

k8s → Contains all Kubernetes configuration files.

🔹 Step 2: Backend API Setup (Node.js)
1️⃣ Navigate to the backend folder
sh
cd backend
2️⃣ Initialize a Node.js project
sh
npm init -y
This creates a package.json file.

3️⃣ Create the backend files
sh
touch server.js package-lock.json Dockerfile
server.js → Main API logic.

package-lock.json → Will be generated when installing dependencies.

Dockerfile → Used to containerize the backend API.

4️⃣ Install dependencies
sh
npm install express pg
This generates package-lock.json and installs required dependencies.

5️⃣ Write the server.js file
Use an editor like nano or VS Code (code .):

sh
nano server.js
Paste this inside:

javascript
const express = require("express");
const { Pool } = require("pg");

const app = express();
const port = process.env.SERVER_PORT || 3001;
const pool = new Pool({
user: process.env.DB_USER,
host: process.env.DB_HOST,
database: process.env.DB_NAME,
password: process.env.DB_PASSWORD,
port: process.env.DB_PORT || 5432
});

app.get("/api/inventory", async (req, res) => {
try {
const result = await pool.query("SELECT \* FROM inventory");
res.json(result.rows);
} catch (err) {
console.error("Error fetching inventory:", err);
res.status(500).send("Database error");
}
});

app.listen(port, () => {
console.log(`Backend API running on port ${port}`);
});
Save and exit (Ctrl+X, then Y).

🔹 Step 3: Frontend Web Setup (PHP & Nginx)
1️⃣ Navigate to the frontend folder
sh
cd ../frontend
2️⃣ Create necessary files
sh
touch index.php Dockerfile nginx.conf
index.php → Serves the frontend and connects to the API.

Dockerfile → Containerizes the frontend.

nginx.conf → Configures Nginx to serve PHP.

3️⃣ Write the index.php file
sh
nano index.php
Paste this:

php

<?php
$apiUrl = getenv('BACKEND_API_URL') . "/api/inventory";
$responseJson = @file_get_contents($apiUrl);
$data = json_decode($responseJson, true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory</title>
</head>
<body>
    <h1>Available Inventory</h1>
    <ul>
        <?php foreach ($data as $item): ?>
            <li><?php echo htmlspecialchars($item['item_name']) . " - $" . htmlspecialchars($item['price']); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
Save and exit (Ctrl+X, then Y).

🔹 Step 4: Create Kubernetes Configuration
1️⃣ Navigate to the k8s folder
sh
cd ../k8s
2️⃣ Create all Kubernetes YAML files
sh
touch postgres-secret.yaml postgres-configmap.yaml api-configmap.yaml frontend-configmap.yaml \
 postgres-statefulset.yaml backend-api-deployment.yaml frontend-deployment.yaml \
 postgres-service.yaml backend-api-service.yaml frontend-service.yaml
3️⃣ Write Kubernetes files
Each file should contain the appropriate configuration.

For example, backend-api-deployment.yaml:

yaml
apiVersion: apps/v1
kind: Deployment
metadata:
name: backend-api
namespace: multi-tier-app
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
containers: - name: backend-api
image: backend-api:v1
imagePullPolicy: Never
ports: - containerPort: 3001
Repeat this for all configuration files.

🔹 Step 5: Start Minikube with Multiple Nodes
sh
minikube start -p multinode-cluster --nodes=3
Verify:

sh
kubectl get nodes
🔹 Step 6: Build and Load Docker Images
Navigate to backend and build the image:

sh
cd ../backend
docker build -t backend-api:v1 .
minikube -p multinode-cluster image load backend-api:v1
Do the same for frontend:

sh
cd ../frontend
docker build -t frontend-web:v1 .
minikube -p multinode-cluster image load frontend-web:v1
🔹 Step 7: Apply Kubernetes Configuration
sh
cd ../k8s
kubectl apply -f postgres-secret.yaml
kubectl apply -f postgres-configmap.yaml
kubectl apply -f api-configmap.yaml
kubectl apply -f frontend-configmap.yaml
kubectl apply -f postgres-statefulset.yaml
kubectl apply -f backend-api-deployment.yaml
kubectl apply -f frontend-deployment.yaml
kubectl apply -f postgres-service.yaml
kubectl apply -f backend-api-service.yaml
kubectl apply -f frontend-service.yaml
Verify:

sh
kubectl get pods -o wide
kubectl get services
🔹 Step 8: Apply SQL Changes to PostgreSQL
Enter the PostgreSQL pod:

sh
kubectl exec -it postgres-db-0 -- psql -U postgres -d ecommerce_db
Create the inventory table:

sql
CREATE TABLE inventory (
item_id SERIAL PRIMARY KEY,
item_name VARCHAR(255) NOT NULL,
description TEXT,
price DECIMAL(10,2) NOT NULL,
quantity INT NOT NULL,
category VARCHAR(100),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Insert test data:

sql
INSERT INTO inventory (item_name, description, price, quantity, category) VALUES
('Smartphone', 'Latest model with 128GB storage', 699.99, 20, 'Electronics'),
('Sneakers', 'Comfortable running shoes', 89.99, 50, 'Footwear'),
('Backpack', 'Durable travel backpack', 59.99, 30, 'Accessories');
Verify:

sql
SELECT \* FROM inventory;
Exit PostgreSQL:

sh
\q
exit
🔹 Step 9: Test API and Frontend
Test API from inside Kubernetes:

sh
kubectl exec -it $(kubectl get pods -l app=backend-api -o jsonpath='{.items[0].metadata.name}') -- curl http://localhost:3001/api/inventory
Expose frontend via Minikube:

sh
minikube service frontend-service -p multinode-cluster --url
Open the generated URL in your browser!
