<?php

/**
 * This File is part of the plesk-acronis extension
 * (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Eitan Schuler <schuler@strato-rz.net>
 *
 * Date: 3/13/16
 * Time: 2:00 PM
 *
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */
class Modules_AcronisBackup_Backups_BackupHelper
{
    /**
     * getBackupHistory
     *
     * Description
     *
     *
     * @return array
     */
    public static function getBackupHistory()
    {

        return array(array(
            "name" => "foo",
            "date"=>  new DateTime(now)
        ),
            array(
                "name" => "bar",
                "date" => new DateTime(now)
            )
        );
    }
}