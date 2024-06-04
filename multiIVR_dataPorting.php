<?php

error_reporting(E_ALL);

include_once '/var/www/html/ncrm/views/crmViews/nayatelCrm/include_files/sendMailGeneric_Response.php';
// include_once("/var/www/html/ncrm/views/crmViews/nayatelCrm/include_files/Database_pdo_StandBy.php");

// include_once "/var/www/html/ncrm/views/crmViews/nayatelCrm/include_files/Db_Standby.php";

include_once "/var/www/html/ncrm/views/crmViews/nayatelCrm/include_files/DbClass.php";
$db = new DbClass();

// $db = new Db_StandBy();

// Database Object\
// var_dump("remove exit");exit;
// $db = new Database();

$query="SELECT userid from NTLCRM.MULTI_IVR_OPTIMIZED_SUMMARY where ont_alarms IS null";
$result= $db->execSelect($query);
// var_dump($result);exit;

for($i =0;$i<count($result);$i++)
{
// autobonus
    // $query="SELECT
    //         CASE
    //             WHEN a.requestdatetime > TRUNC(sysdate, 'MM') AND a.unlockstatus='done' THEN '0'
    //             WHEN a.requestdatetime < TRUNC(sysdate, 'MM') AND a.unlockstatus='done' THEN '1'
    //             ELSE '0' 
    //         END AS Bonus_Status
    //         FROM BONUSREQUESTS a
    //         where userid='".$result[$i]['USERID']."'";

     // outage
    // $query="SELECT DISTINCT userid,status,substatus,
    // CASE
    //    WHEN hours=0 AND minutes=0 AND acc_status='active' AND user_status='ACTIVE' THEN '1'
    //    WHEN remaininghours>0 AND acc_status='active' AND user_status='ACTIVE' THEN '1'
    //    ELSE '0' 
    //  END AS Outage_Status
    //  FROM (
    //    SELECT us.userid,a.hours, a.minutes, us.status AS acc_status,a.status AS user_status,
    //    us.status,us.substatus,
    //    TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') AS currentdatetime,
    //    TO_DATE(a.datetime, 'DD-MM-YYYY HH24:MI:SS') + ((a.HOURS / 24) + (a.MINUTES/ 1440)) AS HOURDATE,
    //    ROUND((TO_DATE(TO_CHAR(a.datetime , 'DD-MM-YYYY HH24:MI:SS'), 'DD-MM-YYYY HH24:MI:SS') + ((a.HOURS / 24) + (a.MINUTES/ 1440)) -
    //    TO_DATE(TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS'), 'DD-MM-YYYY HH24:MI:SS')) * 24, 1) AS remaininghours
    //    FROM eventalerts a
    //    INNER JOIN usereventalert b ON a.eventid = b.eventid
    //    INNER JOIN USERS_STATUS US ON us.userid = b.userid 
    //    WHERE a.event_type = 'Fiber Cut'
    //    ) 
    //    where userid='".$result[$i]['USERID']."'";


    // ONT Status

      $query="  SELECT userid,
        CASE 
        WHEN alarmdescription IN (
            'The distribute fiber is broken or the OLT cannot receive expected optical signals from the GPON ONT(LOSi/LOBi)',
            'The distribute fiber is broken or the OLT cannot receive expected optical signals from the ONT(LOSi/LOBi)',
            'The loss of frame of ONTi (LOFi) occurs',
            'The feeder fiber is broken or OLT cannot receive any expected optical signals(LOS)'
        ) THEN 'ONT_RED'
        
        WHEN alarmdescription IN ('The dying-gasp of GPON ONTi (DGi) is generated') THEN 'ONT_OFF'
        
        ELSE 'Other'
        
        END AS ONT_ALARM

        FROM 

        (SELECT * FROM (SELECT ha.*, ROW_NUMBER() OVER (PARTITION BY ha.userid ORDER BY ha.ID DESC) rn FROM historicalalarms ha) WHERE rn = 1)

        WHERE userid='".$result[$i]['USERID']."'";

    $resTemp= $db->execSelect($query);

    // var_dump($resTemp);exit;
    // if (empty($resTemp))
    // {
    //     $resTemp[0]['BONUS_STATUS']=0;
    // }

        $query="UPDATE NTLCRM.MULTI_IVR_OPTIMIZED_SUMMARY set ont_alarms='".$resTemp[0]['ONT_ALARM']."' where userid='".$result[$i]['USERID']."'";

        // var_dump($query);
    
        $res =$db->execInsertUpdate($query); 
        var_dump($result[$i]['USERID']); 
        // var_dump($res);exit;
}

?>