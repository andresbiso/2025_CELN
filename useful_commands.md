# Guía Rápida de Minikube y Kubernetes

## Comandos Básicos de Minikube

```bash
minikube start
# Inicia un clúster de Kubernetes local. Si no se especifica una versión, utilizará la última versión estable.
```

```bash
minikube status
# Muestra el estado actual del clúster de Minikube. Esto incluye si el clúster está en ejecución y la versión de Kubernetes que está utilizando.
```

```bash
minikube stop
# Detiene el clúster de Minikube.
```

```bash
minikube delete
# Elimina el clúster de Minikube, liberando los recursos asociados.
```

```bash
minikube dashboard
# Abre la interfaz de usuario (dashboard) de Kubernetes en tu navegador web. Esta interfaz te permite visualizar y gestionar tu clúster.
```

```bash
minikube addons list
# Muestra una lista de los complementos que posee Minikube, para aumentar las capacidades del Kubernetes implementado.
```

```bash
minikube addons enable [addon-name]
# Habilita un complemento de Minikube específico.
```

```bash
minikube addons disable [addon-name]
# Deshabilita un complemento de Minikube específico.
```

```bash
minikube service [nombre-servicio]
# Genera un url para acceder a un servicio que se encuentra corriendo dentro de minikube.
```

## Comandos Esenciales de Kubectl

```bash
kubectl get pods
# Lista todos los pods (las unidades más pequeñas desplegables en Kubernetes) en el espacio de nombres actual.
```

```bash
kubectl get deployments
# Lista todos los deployments (despliegues) en el espacio de nombres actual. Los deployments gestionan los pods y aseguran que se mantenga el estado deseado.
```

```bash
kubectl get services
# Lista todos los servicios en el espacio de nombres actual. Los servicios exponen las aplicaciones que se ejecutan en los pods.
```

```bash
kubectl get nodes
# lista los nodos que posee el cluster de Kubernetes, en el caso de minikube, solo será listado 1 nodo.
```

```bash
kubectl create deployment [nombre-despliegue] --image=[imagen]
# Crea un deployment con la imagen de contenedor especificada.
```

```bash
kubectl expose deployment [nombre-despliegue] --type=NodePort --port=[puerto]
# Expone el deployment como un servicio de tipo NodePort para que se pueda acceder a él desde fuera del clúster de Minikube.
```

```bash
kubectl logs [nombre-pod]
# Muestra los registros (logs) de un pod específico.
```

```bash
kubectl apply -f [nombre-archivo].yaml
# Aplica la configuración definida en un archivo YAML para crear o actualizar recursos en el clúster.
```

```bash
kubectl delete -f [nombre-archivo].yaml
# Elimina los recursos definidos en un archivo YAML.
```

```bash
kubectl delete pod [nombre-pod]
# Elimina un pod específico.
```

## Consejos Adicionales

```bash
kubectl describe [recurso] [nombre-recurso]
# Permite obtener información detallada sobre un recurso específico.
# Por ejemplo: `kubectl describe pod mi-pod`
```

```bash
kubectl get namespaces # Listar los espacios de nombres.
kubectl create namespace [nombre-espacio-nombres] # Crear un espacio de nombres nuevo.
# Los espacios de nombres ayudan a organizar los recursos.
```

```bash
alias k=kubectl
# Para facilitar el uso de kubectl, podemos crear alias de los comandos más largos.
```
