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

# :hammer: Configurar y Levantar el entorno - Primera parte de la materia <a name = "configure_run_environment_1"></a>

- Instalar el VirtualBox Extension Pack: https://www.virtualbox.org/wiki/Downloads

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

- Instalar el VirtualBox Extension Pack: https://www.virtualbox.org/wiki/Downloads

## ¿Cómo configurar el entorno?

1. Crear VDI con 40GB e indicar que ocupe todo el espacio (Marcar "Pre-allocate Full Size").
2. Descargar e instalar Ubuntu Server LTS (amd64).
   1. Importante indicarle 4GiB de memoria RAM y 1 CPU core.
   2. Configurarlo con 128 MB de memoria gráfica.
   3. Configurar usuario y contraseña con el valor "nube" (sin comillas dobles).
   4. No es necesario configurar OpenSSH a través del instalador.
   5. No es necesario configurar el disco como LVM.
3. Una vez finalizada la instalación, modificar la vm para que use dos cpu cores.
4. Instalar herramientas: `sudo apt install build-essential curl`
5. Instalar Guest Additions de virtualbox en la vm.

```bash
# [Guía de instalación y configuración](https://gist.github.com/magnetikonline/1e7e2dbd1b288fecf090f1ef12f0c80b)

# Start VM, goto Devices - Insert Guest Additions CD image to mount the ISO image.
# From the terminal, run the following commands:
sudo su
apt install gcc make
mkdir --parents /media/cdrom
mount /dev/cdrom /media/cdrom
/media/cdrom/VBoxLinuxAdditions.run
sudo reboot

# After reboot
modinfo vboxguest
sudo usermod --append --groups vboxsf -- "$USER"
cat /etc/group | grep "$USER"
# Host shares should now be mounted in Ubuntu guest under /media via the installed VBoxService service, set to start on system boot-up.
```

6. Con esto ya podremos compartir carpetas entre el host y el guest a través de la carpeta /media.

A continuación, se indican una guías para la instalación de [Apache Cloud Stack](https://cloudstack.apache.org/) en la VM.

> [!IMPORTANT]
> Seguir los pasos indicados para Ubuntu.

- Guía recomendada: [cloud_stack_intallation_guide.pdf](https://github.com/andresbiso/2025_CELN/blob/main/0_resources/cloud_stack_intallation_guide.pdf)
- Guías alternativas: [cloud_stack_alternative_installation_guides.md](https://github.com/andresbiso/2025_CELN/blob/main/0_resources/cloud_stack_alternative_installation_guides.md)

## Guía Instalación Actualizada

- Esta es la guía recomendada pero actualizada para Ubuntu 24.04 LTS.

> [!NOTE]
> Configurar VM Ubuntu Server en modo "bridged mode". Asegurarse quue Adapter 1 esté en modo Bridged Adapter.

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
> Reemplazar “enp0s3” con la interface ethernet por defecto.

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
    enp0s3:
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
      interfaces: [enp0s3]
      dhcp4: false
      dhcp6: false
      parameters:
        stp: false
        forward-delay: 0
```

```bash
netplan generate
netplan apply
# Si se cae la conexión en este punto,
# volver a conectarse por ssh pero haciendo uso de la nueba ip.
sudo reboot
```

```bash
# Volver a conectarse por ssh pero con la nueva ip
ip a # Tomar nota de la ip
ssh root@your_server_ip # Desde fuera de la vm ejecutar este comando para probar el ssh
# Responder yes
```

```bash
# Cambiar nombre hostname
hostname --fqdn # revisar hostname actual
hostnamectl set-hostname server.local --static
sudo reboot
hostname --fqdn # revisar cambio en hostname
```

```bash
# Instalar Chrony para activar NTP para sincronización de tiempo
apt install chrony
```

```bash
# Configurar storage NFS
apt install nfs-kernel-server quota
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
apt install mysql-server
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
# Instalar cloudstack management server
sudo apt -y install cloudstack-management
update-alternatives --config java # verificar java 17
# cloudstack-setup-databases cloud:password@localhost --deploy-as=root:<root password, default blank> -i <cloudbr0 IP here>
cloudstack-setup-databases cloud:password@localhost --deploy-as=root -i 192.168.1.2
cloudstack-setup-management
```

```bash
# Instalar KVM
# Install KVM and CloudStack agent, configure libvirt:
apt install qemu-kvm cloudstack-agent
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
