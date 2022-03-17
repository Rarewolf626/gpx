# gpx

### Files to copy into deployment

rsync -rv --ignore-existing html-backup/wp-content/plugins/ html/wp-content/plugins

rsync -rv --ignore-existing html-backup/wp-includes  html/

rsync -rv --ignore-existing html-backup/wp-admin  html/

rsync -rv --ignore-existing html-backup/images  html/

rsync -rv --ignore-existing html-backup/*.php  html

rsync w html-backup/images  html/

rsync -rv --ignore-existing wp-content/uploads gpx@50.116.9.62:~/gpxvacations.com/www/html/wp-content/

rsync -rv --ignore-existing images gpx@50.116.9.62:~/gpxvacations.com/www/html/
