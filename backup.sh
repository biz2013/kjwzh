#!/bin/bash
echo "`date -u` run settlement: /usr/bin/curl -v -k https://www.9lp.com/settlement.php"
output=$(/usr/bin/curl -v -k https://www.9lp.com/settlement.php)
echo "`date -u` settlement output is $output"
echo "`date -u` settlement return is $?"

CNYDIR=/tmp/cnyfund/mount
POSBACKUPSQL=pos-backup-$(date +%F).sql
QRCODEBACKUP=qrcode-img-$(date +%F).tar.gz
CNYWALLETBACKUP=pos-cnywallet-$(date +%F).dat
echo "echo `date -u` sudo /usr/bin/mysqldump --defaults-extra-file=/home/ubuntu/.my.cnf --single-transaction --quick --lock-tables=false --all-databases > /home/ubuntu/workspace/kjwzh/backup/$POSBACKUPSQL"
output=$(sudo /usr/bin/mysqldump --defaults-extra-file=/home/ubuntu/.my.cnf --single-transaction --quick --lock-tables=false --all-databases > /home/ubuntu/workspace/kjwzh/backup/$POSBACKUPSQL)
echo "`date -u` backup return $output $?"

echo "`date -u` /bin/tar cvzf /home/ubuntu/workspace/kjwzh/backup/$QRCODEBACKUP /home/ubuntu/workspace/kjwzh/images/upload/weixin"
output=$(/bin/tar cvzf /home/ubuntu/workspace/kjwzh/backup/$QRCODEBACKUP /home/ubuntu/workspace/kjwzh/images/upload/weixin)
echo "`date -u` backup return $output $?"


echo "`date -u` sudo /home/ubuntu/.local/bin/aws s3 cp /home/ubuntu/workspace/kjwzh/backup/$POSBACKUPSQL s3://elasticbeanstalk-us-west-2-551441213847/POSBackup/"
output=$(/home/ubuntu/.local/bin/aws s3 cp /home/ubuntu/workspace/kjwzh/backup/$POSBACKUPSQL s3://elasticbeanstalk-us-west-2-551441213847/POSBackup/)

echo "`date -u` upload return $output $?"

echo "`date -u` sudo /home/ubuntu/.local/bin/aws s3 cp /home/ubuntu/workspace/kjwzh/backup/$QRCODEBACKUP s3://elasticbeanstalk-us-west-2-551441213847/POSBackup/"
output=$(/home/ubuntu/.local/bin/aws s3 cp /home/ubuntu/workspace/kjwzh/backup/$QRCODEBACKUP s3://elasticbeanstalk-us-west-2-551441213847/POSBackup/)

echo "`date -u` upload return $output $?"

if [ -d "$CNYDIR" ]; then
  echo "`date -u` backup cnywallet files"
  echo "`date -u` cd $CNYROOT"
  cd $CNYROOT
  echo "`date -u` sudo /usr/bin/docker exec 390 /opt/cnyfund/bin/cnyfund -datadir=/cnyfund -conf=/cnyfund/cnyfund.conf backupwallet /cnyfund/$CNYWALLETBACKUP"
  sudo /usr/bin/docker exec 390 /opt/cnyfund/bin/cnyfund -datadir=/cnyfund -conf=/cnyfund/cnyfund.conf backupwallet /cnyfund/$CNYWALLETBACKUP
  sudo mv /tmp/cnyfund/mount/$CNYWALLETBACKUP /home/ubuntu/workspace/kjwzh/backup/$CNYWALLETBACKUP 
  sudo chown ubuntu:ubuntu /home/ubuntu/workspace/kjwzh/backup/$CNYWALLETBACKUP
output=$(/home/ubuntu/.local/bin/aws s3 cp /home/ubuntu/workspace/kjwzh/backup/$CNYWALLETBACKUP s3://elasticbeanstalk-us-west-2-551441213847/POSBackup/)
fi
