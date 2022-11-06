# auth
Running of YII2 (RESTful Api on port 8080):	
```
cd path/to/auth/basic
php yii serve 0.0.0.0
```

Running of VUEJS 3 (UI on port 5173): 
```
cd path/to/auth/ui
npm run dev
```

Running DOCKER containers for NGINX (:80) + MYSQL (:3306):
```
cd path/to/auth/docker
docker-compose up -d
```

Checking of availability internal proxy services (expected):
```
/ # nc -vz host.docker.internal:5173
host.docker.internal:5173 (192.168.65.2:5173) open
/ # nc -vz host.docker.internal:8080
host.docker.internal:8080 (192.168.65.2:8080) open
```

