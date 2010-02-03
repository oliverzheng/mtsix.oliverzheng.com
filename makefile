DEST := ~/domains/mtsix.oliverzheng.com/

DB_NAME :=
DB_USER :=
DB_PASSWORD :=
DB_HOST :=

deploy:
	mkdir -p $(DEST)
	bash -c "shopt -s dotglob; cp -rf files/* $(DEST)"
	cat files/wp-config.php \
        | sed s/CONFIG_DB_NAME/$(DB_NAME)/g \
	    | sed s/CONFIG_DB_USER/$(DB_USER)/g \
	    | sed s/CONFIG_DB_PASSWORD/$(DB_PASSWORD)/g \
	    | sed s/CONFIG_DB_HOST/$(DB_HOST)/g \
        > $(DEST)/wp-config.php
