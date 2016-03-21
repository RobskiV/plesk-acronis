<?php
/**
 * This File is part of the plesk-acronis extension
 * (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Eitan Schuler <schuler@strato-rz.net>
 *
 * Date: 3/13/16
 * Time: 2:41 PM
 *
 * Creates Meta-XML containing informations about each subscriptions managed by plesk
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

require_once(__DIR__ . '/../library/subscriptions/SubscriptionHelper.php');
require_once(__DIR__ . '/../library/databases/DatabaseHelper.php');

$client = pm_Client::getByLogin("admin");
$version = Modules_AcronisBackup_subscriptions_SubscriptionHelper::getPleskVersion();
$subscriptionDbs = Modules_AcronisBackup_databases_DatabaseHelper::getDatabases($client);

$metadataFile = fopen("/usr/local/psa/var/modules/acronis-backup/metadata.xml", "w");
fwrite($metadataFile, "<metadata>
  <extension>
    <name>acronis-backup</name>
    <version>1.0</version>
  </extension>
  <pleskVersion>$version</pleskVersion>
  <subscriptions>");

foreach ($subscriptionDbs as $key => $subscriptionDb) {
    fwrite($metadataFile, "
    <subscription>
      <name>" . $key . "</name>
      <databases>");
    foreach ($subscriptionDb as $instance) {
        if (isset($instance)) {
            fwrite($metadataFile, "
        <database>" . $instance . "</database>");
        }
    }
    fwrite($metadataFile, "
      </databases>
    </subscription>");
}

fwrite($metadataFile, "
  </subscriptions>
</metadata>");

fclose($metadataFile);
