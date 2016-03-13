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
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */
require_once('../library/subscriptions/SubscriptionHelper.php');

$client = pm_Client::getByLogin("admin");
$version = Modules_AcronisBackup_Subscriptions_SubscriptionHelper::getPleskVersion();
$subscriptions = Modules_AcronisBackup_Subscriptions_SubscriptionHelper::getSubscriptions($client);

$metadataFile = fopen("/usr/local/psa/var/modules/acronis-backup/metadata.xml", "w");
fwrite($metadataFile, "<metadata>
  <extension>
    <name>acronis-backup></name>
    <version>1.0</version>
  </extension>
  <pleskVersion>$version</pleskVersion>
  <subscriptions>");
foreach ($subscriptions as $subscription){
    fwrite($metadataFile, "<subscription>" . $subscription . "</subscription>");
}
fwrite($metadataFile,"</subscriptions></metadata>");
fclose($metadataFile);
