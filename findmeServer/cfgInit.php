<?php

/**
 * 配置设定
 */
define('WEB_ROOT',__DIR__);
require 'Application/Common/Common/function.php';
$cfgDefaultFile = 'Application/Common/Conf/defaultConfig.php';
$localConfigFile = 'data/local_specialConfig.php';
$fixEnumFile = 'Application/Common/Conf/baseEnum.php';
$lockFile = 'starjoyCfgLock';
$fixCfg=false;
if ($fixCfg && file_exists($lockFile)) {
    $return['status'] = 1;
    $return['msg'] = 'the config is locked';
    echo json_encode($return);
    die;
}

$defaultArr = readConfig($cfgDefaultFile);
$localArr   = readConfig($localConfigFile);

if ($_POST && $_POST['act'] == 'set') {
    $return = array('status' => 0, 'msg' => 'success');
    if ($fixCfg && file_exists($lockFile)) {
        $return['status'] = 1;
        $return['msg'] = 'the config is locked';
        echo json_encode($return);
        die;
    }
    unset($_POST['act']);
    writeConfig($localConfigFile, $_POST,$defaultArr,$lockFile);
    echo json_encode($return);
    die;
}
?>
<html>
<head>
    <meta charset="utf-8">
    <script src="Public/js/jquery-2.1.4.min.js"></script>
    <script>
        $(document).ready(function () {
                window.console.log('lllll');
                $('#submitBtn').on('click', function () {
                    window.console.log('cccccccccc');
                    var data = $('#cfgSet').serialize();
                    data += '&act=set';
                    $.post('cfgInit.php', data, function (result) {
                        if (result.status == 0) {
                            alert("配置设置成功");
                            //location.reload();
                        } else {
                            alert(result.msg);
                        }
                    }, 'json');
                });
            }
        );
    </script>
    <style>
        .desc{color:gray;vertical-align: top;font-size:10pt;margin-left:1rem}
        .title{display: inline-block;text-align: right;}
        .longText{width:500px}
        textarea{width:500px;height:200px}
    </style>
</head>
<body>
<?php
/**
 *
 *
 *
 *
 * Created by www.soe-soe.com
 * Author: shijy
 * Date: 2016/9/1
 * Time: 15:05
 */

//预定义的枚举常量
$preDefineArr = readConfig($fixEnumFile);
echo "<form id='cfgSet' />";
$lastOne = null;
$lastKey = null;
foreach ($defaultArr as $k => $v) {
    if(array_key_exists($lastKey, $localArr) ){
        $localValue=$localArr[$lastKey][0];
    }else{
        $localValue=$lastOne[0];
    }
    $localValue=str_replace('\'','',$localValue);

    if($lastOne == null){
        $lastOne = dealCurrent($v);
        $lastKey = $k;
        continue;
    }

    if(is_array($lastOne)){
        if($lastOne['authority'] >0){
            //名称
            echo "<span class='title'>{$lastOne['title']}:</span>";
            if (!$lastOne['format']) {
                $lastOne['format'] = 'T';
            }
            $lastOne['format'] = trim($lastOne['format']);
            @list($baseType, $ext) = explode('=', $lastOne['format']);
            switch (strtoupper($baseType)) {
                case 'PE':
                    echo "<select name='{$lastKey}'>";
                    foreach ($preDefineArr as $pk => $pv) {
                        //$ext=trim($ext);
                        if (stripos($pk, $ext) === false) {
                            continue;
                        }
                        $tv = str_replace('\'','',$pv[0]);
                        echo "<option value='{$tv}' ";
                        if ($localValue == $tv) {
                            echo 'selected';
                        }
                        echo ">{$pv[1]}</option>";
                    }
                    echo "</select>";
                    break;
                case 'EE':
                    if ($ext) {
                        $params = explode(',', $ext);
                        echo "<select name='{$lastKey}'>";

                        foreach ($params as $one) {
                            @list($tval, $ttxt) = explode(':', $one);
                            echo "<option value='{$tval}' ";
                            if ($localValue == $tval) {
                                echo 'selected';
                            }
                            echo ">{$ttxt}</option>";
                        }
                        echo "</select>";
                    } else {
                        die("{$lastOne['title']}配置模板格式错误");
                    }


                    break;
                case 'I':
                    echo "<input name='{$lastKey}' value='{$localValue}' type='number' />";
                    break;
                case 'T':
                    echo "<input name='{$lastKey}' value='{$localValue}' type='text' />";
                    break;
                case 'LT':
                    echo "<input name='{$lastKey}' value='{$localValue}' type='text' class='longText'/>";
                    break;
                default:
                    echo "<input name='{$lastKey}' value='{$localValue}' type='text' />";


            }


            //备注说明
            if ($lastOne['mem'] && strlen($lastOne['mem']) > 1) {
                echo "<span class='desc'>{$lastOne['mem']}</span>";
            }

            //是否增加项
            if (!array_key_exists($lastKey, $localArr)) {
                echo "<span style='color:red'>*新增</span>";
            }
            echo '<br />';
        }
        $lastOne = dealCurrent($v);
        $lastKey = $k;
    } else{
        $val = dealCurrent($v);
        if(is_array($val) and array_key_exists('authority',$val) and $val['authority'] > 0){
            $v=substr($lastOne,2);
            echo "<hr /><h3>{$v}</h3>";
        }
        $lastOne = $val;
        $lastKey = $k;
    }
}
echo "</form>";
echo "<br /><input type='button' value='提交' id='submitBtn'>";
?>
</body>
</html>
