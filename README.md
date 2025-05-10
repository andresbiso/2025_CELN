<p align="center">
    Computación en la Nube y Procesamiento Distribuido - UP
    <br>
    1C - 2025
    <br>
</p>

# :pencil: Table of Contents

- [Acerca De](#about)
- [Aplicaciones Utilizadas](#applications)
- [Configurar y levantar el entorno - Primera parte de la materia](#configure_run_environment_1)
- [Configurar y levantar el entorno - Segunda parte de la materia](#configure_run_environment_2)
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

- Instalar el VirtualBox Extension Pack: https://www.virtualbox.org/wiki/Downloads

- [vmware-fusion](https://formulae.brew.sh/cask/vmware-fusion#default)

```
brew install --cask vmware-fusion
```

# :hammer: Configurar y Levantar el entorno - Primera parte de la materia <a name = "configure_run_environment_1"></a>

## ¿Cómo configurar el entorno?

1. Crear VDI con 45GB e indicar que ocupe todo el espacio.
2. Descargar e instalar Ubuntu LTS (amd64).
   1. Importante indicarle 4GiB de memoria RAM y 1 CPU core.
   2. Configurarlo con 128 MB de memoria gráfica.
   3. Configurar usuario y contraseña con el valor "nube" (sin comillas dobles)
3. Una vez finalizada la instalación, modificar la vm para que use dos cpu cores.
4. Instalar herramientas: `sudo apt install build-essential curl`
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

### Problemas con archivos YAML

- Validar archivos en el siguiente sitio: https://codebeautify.org/yaml-validator

## Convertir a Base64

- https://cyberchef.io/
- Linux: `echo -n "value" | base64`

# :hammer: Configurar y Levantar el entorno - Segunda parte de la materia <a name = "configure_run_environment_2"></a>

> [!NOTE]
> Para esta segunda parte vamos a estar usando VMware Fusion Pro.
> Esto se debe a que VirtualBox todavía no soporta Nested Virt en macOS.

## ¿Cómo configurar el entorno?

1. Descargar Ubuntu Server LTS (amd64)
2. Mover la ISO a VMware Fusion Pro.
   1. Se debería configurar automáticamente con: 20GB de disco, 4096MiB (4GiB) de memoria RAM y 2 CPU core.
   2. Ir a "Processors & Memory", ir a opciones avanzadas y activar "Enable hypervisor...".
   3. Ir a "Processors & Memory", incrementar memoria a 8192MiB (8GiB).
   4. Ir a "Network Adapter" y seleccionar Autodetect para el modo bridged networking.
   5. Ir a "Hard Disk", aumentar tamaño de disco a 40GB y marcar "pre-allocate disk space".
3. Instalar Ubuntu Server.
   1. Configurar usuario y contraseña con el valor "nube" (sin comillas dobles).
   2. No es necesario configurar OpenSSH a través del instalador.
   3. No es necesario configurar el disco como LVM.
4. Instalar herramientas: `sudo apt update && sudo apt install -y build-essential binutils linux-headers-$(uname -r)`
5. Instalar VMware Tools: https://knowledge.broadcom.com/external/article/315313/installing-VMware-tools-in-an-ubuntu-vir.html
   1. Hay veces que se instala automáticamente. Revisar con: `VMware-toolbox-cmd -v`
6. Habilitar carpetas compartidas: https://techdocs.broadcom.com/us/en/VMware-cis/desktop-hypervisors/fusion-pro/13-0/using-VMware-fusion/sharing-files-between-windows-and-your-mac/add-a-shared-folder.html
   1. Seguir los pasos para que se hagan auto mount modificando /etc/fstab: https://askubuntu.com/a/1051620
   2. Luego de ejecutar `sudo mount -a` y `systemctl daemon-reload` deberíamos ver las carpetas compartidas en /mnt/hgfs/.
7. Habilitar clipboard compartido: Con la VM apagada ir a settings -> isolation y habilitar las opciones de clipboard.

A continuación, se indican una guías para la instalación de [Apache Cloud Stack](https://cloudstack.apache.org/) en la VM.

> [!IMPORTANT]
> Recomiendo seguir la guía actualizada que se puede encontrar en este mismo archivo.
> En caso de querer hacer uso de las otras guías, seguir los pasos para Ubuntu.

- Guía recomendada: [cloud_stack_intallation_guide.pdf](https://github.com/andresbiso/2025_CELN/blob/main/0_resources/cloud_stack_intallation_guide.pdf)
- Guías alternativas: [cloud_stack_alternative_installation_guides.md](https://github.com/andresbiso/2025_CELN/blob/main/0_resources/cloud_stack_alternative_installation_guides.md)

## Guía de instalación y configuración de CloudStack (Actualizada)

- Esta es la guía recomendada pero actualizada para Ubuntu Server 24.04 LTS.

> [!NOTE]
> Configurar VM Ubuntu Server en modo "bridged mode".

```bash
sudo add-apt-repository universe
sudo apt update
```

```bash
sudo apt install openntpd openssh-server sudo vim htop tar net-tools -y
```

```bash
sudo nano /etc/ssh/sshd_config
# PermitRootLogin yes
sudo systemctl restart ssh
sudo passwd root # Cambiar root password a "nube"
```

```bash
# Deshabilitar firewall
sudo systemctl stop ufw
sudo systemctl disable ufw
```

```bash
ip a # Tomar nota de la ip
ssh root@your_server_ip # Desde fuera de la vm ejecutar este comando para probar el ssh
# Responder yes
```

- A partir de este momento, podemos manejar el ubuntu server desde fuera de la VM. Por lo que tenemos acceso a, por ejemplo, copy-paste.

```bash
apt install bridge-utils
touch /etc/netplan/01-netcfg.yaml
sudo chmod 600 /etc/netplan/01-netcfg.yaml # solo permisos para root
ls -l /etc/netplan/
```

> [!NOTE]
> Reemplazar “ens33” con la interface ethernet por defecto.

```bash
# Revisar nombre interface
ip link show
# Editar archivo
nano /etc/netplan/01-netcfg.yaml
```

```yaml
network:
  version: 2
  renderer: networkd
  ethernets:
    ens33:
      dhcp4: false
      dhcp6: false
      optional: true
  bridges:
    cloudbr0:
      addresses: [192.168.1.2/24]
      routes:
        - to: 0.0.0.0/0
          via: 192.168.1.1 # Your physical/home router
      nameservers:
        addresses: [8.8.8.8]
      interfaces: [ens33]
      dhcp4: false
      dhcp6: false
      parameters:
        stp: false
        forward-delay: 0
```

```bash
netplan generate
netplan apply
# Si se cae la conexión en este punto:
sudo reboot # Dentro de la VM
```

```bash
# Volver a conectarse por ssh pero con la nueva ip
ip a # Tomar nota de la ip
ssh-keygen -R your_server_ip # Desde fuera de la vm remover la clave desactualizada
ssh root@your_server_ip # Desde fuera de la vm ejecutar este comando para probar el ssh
# Responder yes
```

```bash
# Cambiar nombre hostname
hostname --fqdn # revisar hostname actual
hostnamectl set-hostname server.local --static

hostname --fqdn # revisar cambio en hostname
```

```bash
# Instalar Chrony para activar NTP para sincronización de tiempo
apt install chrony -y
# Revisar que chronyd esté levantado y responda a los comandos
systemctl status chronyd
chronyc tracking
```

```bash
# Configurar storage NFS
apt install nfs-kernel-server quota -y
# Create exports:
echo "/export/primary *(rw,async,no_root_squash,no_subtree_check)" >> /etc/exports
echo "/export/secondary *(rw,async,no_root_squash,no_subtree_check)" >> /etc/exports
mkdir -p /export/primary /export/secondary
exportfs -a
# Configure and restart NFS server:
sed -i -e 's/^RPCMOUNTDOPTS="--manage-gids"$/RPCMOUNTDOPTS="-p 892 -- manage-gids"/g' /etc/default/nfs-kernel-server
sed -i -e 's/^STATDOPTS=$/STATDOPTS="--port 662 --outgoing-port 2020"/g' /etc/default/nfs-common
echo "NEED_STATD=yes" >> /etc/default/nfs-common
sed -i -e 's/^RPCRQUOTADOPTS=$/RPCRQUOTADOPTS="-p 875"/g' /etc/default/quota
service nfs-kernel-server restart
```

```bash
# Instalación MySQL
apt update -y
apt install mysql-server -y
# Tratar de conectarse como root a mysql.
# En general, no tiene un password asociado en una instalación de cero.
# sudo mysql -u root
# exit
```

```bash
# Configure InnoDB settings in mysql server’s
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```config
[mysqld]
# Agregar valores al final del archivo
innodb_rollback_on_timeout=1
innodb_lock_wait_timeout=600
max_connections=350
log-bin=mysql-bin
binlog-format = 'ROW'
```

> [!NOTE]
> innodb_rollback_on_timeout=1: Esto garantiza que las transacciones se reviertan si alcanzan un tiempo de espera. Es una buena práctica para la consistencia de los datos.
> innodb_lock_wait_timeout=600: Define el tiempo de espera para los bloqueos de fila.
> max_connections=350: Establece el número máximo de conexiones simultáneas.
> log-bin=mysql-bin & binlog-format='ROW': Habilitan el registro binario y la replicación basada en filas, lo que es útil para la recuperación en un punto específico en el tiempo.

```bash
sudo systemctl restart mysql
```

```bash
# Configurar CloudStack Package Repository
# Comenta la guía que en este punto podríamos cambiar estos valores
# por los de una versión más reciente
sudo nano /etc/apt/sources.list.d/cloudstack.list
# Agregar esta línea
deb https://download.cloudstack.org/ubuntu noble 4.20
# Ejecutar este comando para descargar la clave pública
wget -O - https://download.cloudstack.org/release.asc | sudo tee /etc/apt/trusted.gpg.d/cloudstack.asc
sudo apt update
```

```bash
# Instalar CloudStack management server
sudo apt -y install cloudstack-management
update-alternatives --config java # verificar java 17
# cloudstack-setup-databases cloud:password@localhost --deploy-as=root:<root password, default blank> -i <cloudbr0 IP here>
cloudstack-setup-databases cloud:password@localhost --deploy-as=root -i 192.168.1.2
cloudstack-setup-management
```

```bash
# Instalar KVM
# Install KVM and CloudStack agent, configure libvirt:
apt install qemu-kvm cloudstack-agent -y
# Enable VNC for console proxy:
sed -i -e 's/\#vnc_listen.*$/vnc_listen = "0.0.0.0"/g' /etc/libvirt/qemu.conf
# Configure default libvirtd config
echo 'listen_tls=0' >> /etc/libvirt/libvirtd.conf
echo 'listen_tcp=1' >> /etc/libvirt/libvirtd.conf
echo 'tcp_port = "16509"' >> /etc/libvirt/libvirtd.conf
echo 'mdns_adv = 0' >> /etc/libvirt/libvirtd.conf
echo 'auth_tcp = "none"' >> /etc/libvirt/libvirtd.conf
systemctl restart libvirtd
# Disable apparmour on libvirtd
ln -s /etc/apparmor.d/usr.sbin.libvirtd /etc/apparmor.d/disable/
ln -s /etc/apparmor.d/usr.lib.libvirt.virt-aa-helper /etc/apparmor.d/disable/
apparmor_parser -R /etc/apparmor.d/usr.sbin.libvirtd
apparmor_parser -R /etc/apparmor.d/usr.lib.libvirt.virt-aa-helper
# Check KVM is running
lsmod | grep kvm
```

A partir de este momento CloudStack ya está se encuentra instalado.

> [!IMPORTANT]
> Se recomienda en este momento:
>
> 1. Apagar la máquina virtual.
> 2. Realizar un snapshot de la VM antes de continuar con la configuración.

## Verificar acceso a CloudStack

> [!NOTE]
> Nos manejaremos con la interfaz web expuesta a través de un navegador web.

1. En un navegador web acceder a http://192.168.1.2:8080/client/
2. Acceder con username = "admin" y password = "password" (sin las comillas dobles).
3. Verificar que el navegador nos muestre el dashboard de CloudStack.

## Comandos Útiles

```bash
# Reiniciar ubuntu server
sudo reboot
```

```bash
# Apagar ubuntu server
sudo shutdown now
```

## ¿Cómo levantar el entorno?

## Problemas Conocidos

### Agrandar fuente de Ubuntu Server

```bash
# https://askubuntu.com/questions/1402246/how-change-the-font-size-in-ubuntu-server-20-04-lts

# Edit the file /etc/default/console-setup
sudo nano /etc/default/console-setup

# Enter these values:
FONTFACE="Terminus"
FONTSIZE="16x32"

# After changes the values execute:
sudo update-initramfs -u
sudo reboot
```

# :speech_balloon: Autor <a name = "author"></a>

- [@andresbiso](https://github.com/andresbiso)

# :tada: Reconocimientos <a name = "acknowledgement"></a>

- https://github.com/github/gitignore
- https://gist.github.com/rxaviers/7360908 -> github emojis
- https://gist.github.com/Myndex/5140d6fe98519bb15c503c490e713233 -> github flavored markdown cheat sheet
