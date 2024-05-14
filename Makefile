app-bash:
	docker exec -it -u0 loteria_app bash

db:
	docker exec -it -u0 loteria_db mysql -u loteria_admin -p