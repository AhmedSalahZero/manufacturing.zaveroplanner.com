            git status
            git stash
            git pull origin master
           
            /usr/local/bin/ea-php81 artisan view:clear
            chmod -R 775 storage
            chmod -R 775 bootstrap/cache
            chmod 777 -R storage/*
            /usr/local/bin/ea-php81 artisan migrate --force
            
        
            supervisord -c /etc/supervisord.conf
            supervisorctl restart queue-worker:*
           
