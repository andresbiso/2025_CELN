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
