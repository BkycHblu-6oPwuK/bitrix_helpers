# prod

1. сервер на bitrix vm с установленным композером, node js 24
2. nginx должен быть модифицирован чтобы корректно открывать bitrix|local из под php, upload по дефолту должна отдаваться как статика. В www/nuxt через pm2 должен быть запущен node js сервер и по умолчанию все маршруты nginx должны на сервер node js проксироваться
3. ~ git pull, composer i, npm i, npm run build, pm2 reload nuxt-ssr.

модификация сервера:
файлы лежат в ./nginx-prod, их закинуть туда, куда указано в комментарии на 1 строке

## установка ноды

установка nvm

curl -fsSL https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash

source ~/.bashrc
или
source ~/.profile

установка ноды

nvm install 24

npm install -g pm2

## swap

Если оперативки мало

```bash
free -h
swapon --show
```
Если пусто — значит swap не настроен.

создаем:
```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
free -h
```

и добавить строку в /etc/fstab

```
/swapfile none swap sw 0 0
```

## автозапуск

```bash
cd /home/bitrix/www/nuxt
pm2 start ecosystem.config.cjs
pm2 status
pm2 startup
```
pm2 выведет команду вида:
sudo env ...

Эту команду нужно скопировать и выполнить целиком.

```bash
pm2 save
```

после ребута сервера процессы должны быть запущены сами

В итоге после пула запускайте `build.sh`

на тестовом 

```bash
./build.sh --pm2 ecosystem.dev.config.cjs
```

## logs

```bash
pm2 install pm2-logrotate
```

```bash
pm2 set pm2-logrotate:max_size 10M
pm2 set pm2-logrotate:retain 10
pm2 set pm2-logrotate:compress true
pm2 set pm2-logrotate:rotateInterval '0 0 * * *'
pm2 save
```

или оставить по умолчанию