<p align="center">
    Computación en la Nube y Procesamiento Distribuido - UP
    <br>
    1C - 2025
    <br>
</p>

# :pencil: Table of Contents

- [Acerca De](#about)
- [Aplicaciones Utilizadas](#applications)
- [Configurar y levantar el entorno](#configure_run_environment)
- [Autor](#author)
- [Reconocimientos](#acknowledgement)

# :information_source: Acerca De <a name = "about"></a>

- Repositorio para la materia Computación en la Nube y Procesamiento Distribuido de la Universidad de Palermo.

# :hammer: Aplicaciones Utilizadas <a name = "applications"></a>

## macOS

Recomiendo utilizar [homebrew](https://brew.sh/) para instalar estos paquetes:

- [virtualbox](https://formulae.brew.sh/cask/virtualbox#default)

```
brew install --cask virtualbox
```

# :hammer: Configurar y Levantar el entorno <a name = "configure_run_environment"></a>

- Instalar el VirtualBox Extension Pack: https://www.virtualbox.org/wiki/Downloads

## ¿Cómo configurar el entorno?

1. Crear VDI con 45GB e indicar que ocupe todo el espacio.
2. Descargar e instalar Ubuntu LTS (amd64).
   1. Importante indicarle 4GiB de memoria RAM y 1 CPU core.
   2. Configurarlo con 128 MB de memoria gráfica.
   3. Configurar usuario y contraseña con el valor "nube" (sin comillas dobles)
3. Una vez finalizada la instalación, modificar la vm para que use dos cpu cores.
4. Instalar herramientas: build-essential, curl.
5. Instalar snaps (App Center): visual studio code, postman.
6. Instalar Guest Additions de virtualbox en la vm.
   1. La iso se monta en /media/nube/VBox_GAs_x.y.z
   2. Navegar en una terminal a esa ruta y ejecutar: sudo ./VBoxLinuxAdditions.run
7. Configurar usuario: sudo adduser nube vboxsf
   1. Con esto ya podremos compartir carpetas entre el host y el guest a través de la carpeta /media.
   2. Configurar clipboard compartido: https://superuser.com/questions/42134/how-do-i-enable-the-shared-clipboard-in-virtualbox
   3. Configurar carpeta compartida: https://askubuntu.com/questions/161759/how-to-access-a-shared-folder-in-virtualbox
8. Instalar docker: https://docs.docker.com/engine/install/ubuntu/
9. Instalar kubectl: https://kubernetes.io/docs/tasks/tools/install-kubectl-linux/#install-using-native-package-management
10. Instalar minikube: https://minikube.sigs.k8s.io/docs/start/?arch=%2Flinux%2Fx86-64%2Fstable%2Fdebian+package
11. En una terminal ejecutar: `sudo usermod -aG docker $USER && newgrp docker`

## ¿Cómo levantar el entorno (minikube)?

1. Abrir una terminal.
2. Ejecutar: `eval $(minikube docker-env)`
3. Ejecutar: `minikube start --driver=docker`

Puede que en el paso tres se agreguen más opciones. Con esto tendríamos los mínimo necesario para levantar los ejercicios.

## Problemas Conocidos

### Error de user en Docker al iniciar minikube

```bash
sudo usermod -aG docker $USER && newgrp docker
```

# :speech_balloon: Autor <a name = "author"></a>

- [@andresbiso](https://github.com/andresbiso)

# :tada: Reconocimientos <a name = "acknowledgement"></a>

- https://github.com/github/gitignore
- https://gist.github.com/rxaviers/7360908 -> github emojis
