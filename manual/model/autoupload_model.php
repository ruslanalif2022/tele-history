<?php
class Autoupload_model extends Model
{

    public function __construct()
    {
        parent::Model();
        $this->tsm_incharge = '883';
    }

    function chk_enginestate($x)
    {
        $sel = 'engine' . $x . '_stat';
        $this->db->select($sel);
        $qObj = $this->db->get('tb_autoupload_engine');
        $qArr = $qObj->row_array();

        return $qArr[$sel];
    }

    function upd_enginestatus($x = 1, $stat = 0)
    {
        $upd = array(
            'engine' . $x . '_stat' => $stat
        );
        $this->db->update('tb_autoupload_engine', $upd); 
    }

    function check_record($basename)
    {
        $flag = 0;

        if (!empty($basename)) {
            $this->db->where('filename', $basename);
            $qObj = $this->db->get('tb_autoupload_log');
            $flag = $qObj->num_rows() > 0 ? 1 : 0;
        }
        return $flag;
    }

    function getXlsxData($fullpath)
    {
        $return = array();
        if (is_file($fullpath)) {
            $this->load->library('Xlsx_loadv2');
            $return = $this->xlsx_loadv2->read_xlsx($fullpath);
        }
        return $return;
    }

    function autoupload_byengine($excelData, $period, $engine, $basename = '')
    {

        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview', $previewrow);
            }
        }

        ## update target campaign
        if ($engine['engine'] == 'ib' || $engine['engine'] == 'msl') { ## Khusus ib & msl
            $sql = "
                UPDATE tb_uploadpreview
                SET target_campaign = UPPER('{$basename}') ";
            $this->db->simple_query($sql);
            // echo "<br> running ib & msl <br>";
        } else {
            $sql = "
                UPDATE tb_uploadpreview
                SET target_campaign = CONCAT(UPPER('{$engine['partner']}'), ' ', '{$period_month} ', '{$period_year}')
            ";
            $this->db->simple_query($sql);
        }

        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        if ($engine['engine'] == 'ib' || $engine['engine'] == 'msl') {
            $this->make_campaign($basename, $period,  $engine['engine']);
            // echo "<br> running make campaign ib & msl <br>";
        } else { //var_dump($qArr); exit();
            foreach ($qArr as $campaigns) {
                $this->make_campaign($campaigns['campaign'], $period,  $type = 'ntb');
            }
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview.target_campaign = tb_campaign.name
                SET tb_uploadpreview.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## Start mapping to real table
        //$inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'tkp':
                $res = $this->mapping_tkp($engine['partner']);
                break;
            case 'bli':
                $res = $this->mapping_bli($engine['partner']);
                break;
            case 'shp':
                $res = $this->mapping_shp($engine['partner']);
                break;
            case 'map':
                $res = $this->mapping_map($engine['partner']);
                break;
            case 'anw':
                $res = $this->mapping_anw($engine['partner']);
                break;

            case 'apn':
                $res = $this->mapping_apn($engine['partner']);
                break;
            case 'gld':
                $res = $this->mapping_gld($engine['partner']);
                break;
            case 'atk':
                $res = $this->mapping_atk($engine['partner']);
                break;
            case 'ref':
                $res = $this->mapping_ref($engine['partner']);
                break;
            case 'ib':
                $res = $this->mapping_ib($engine['partner']);
                break;
            case 'msl':
                $res = $this->mapping_msl($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function autoupload_byengineproduct($excelData, $period, $engine, $namcampaign)
    {

        $enginenumber = 3;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_cop');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_cop', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_cop
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_cop
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproduct($campaigns['campaign'], $period,  $type = 'cop');
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_cop
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview_cop.target_campaign = tb_campaign.name
                SET tb_uploadpreview_cop.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## Start mapping to real table
        //$inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'cp':
                $res = $this->mapping_cp($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function autoupload_byengineproduct1($excelData, $period, $engine, $namcampaign)
    {

        $enginenumber = 4;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproducttrp($campaigns['campaign'], $period,  $type = 'trp');
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview.target_campaign = tb_campaign.name
                SET tb_uploadpreview.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## Start mapping to real table
        //$inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'trp':
                $res = $this->mapping_trp($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function autoupload_byengineproducttmrw($excelData, $period, $engine, $namcampaign)
    {

        $enginenumber = 5;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = substr($period, 2, 2);
        $period_year = substr($period, 4, 2);

        $namecampaign = 'TMRW XSELL U-SAVE';

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview', $previewrow);
            }
        }



        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview
            SET target_campaign = CONCAT(UPPER('{$namecampaign}'), ' ', '{$period_month}','{$period_year}')
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproductxtmrw($campaigns['campaign'], $period,  $type = 'xtmrw');
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview.target_campaign = tb_campaign.name
                SET tb_uploadpreview.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## Start mapping to real table
        //$inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'xtmrw':
                $res = $this->mapping_xtmrw($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function make_campaign($campaignname, $period, $type)
    {

        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);


        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        if ($type == 'ntb') { ## NTB
            $campaign_product = '39';
            $campaign_type = '1';
            $db_type = 4;
        } else if ($type == 'ib' || $type == 'msl') {
            $campaign_product = '39';
            $campaign_type = '1';
            $db_type = 5;
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '1'; //DEFAULT
            $db_type = 0;
        }

        if ($qObj->num_rows() == 0) { ## create new campaign
            $begindate = DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year'])));
            if ($type == 'ib' || $type == 'msl') {
                $enddate   = DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 1, intval($period_arr['day']) + 1, intval($period_arr['year'])));
            } else {
                $enddate   = DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 6, 1, intval($period_arr['year'])));
            }

            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => $db_type,
                'begindate' => $begindate,
                'enddate' => $enddate,
                'remark' => 'AutoUpload ' . date('Y-m-d H:i:s'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => $campaign_type
            );
            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    function make_campaignproduct($campaignname, $period, $type)
    {

        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $tkcampaign = substr($campaignname, 9, 2);
        $ntkcampaign = substr($campaignname, 9, 3);


        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        if ($type == 'cop') { ## cop
            $campaign_product = '46';
            $campaign_type = '1';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }

        $dbtype = '';  // Declare dulu biar ga error
        if ($tkcampaign == 'TK') {
            $dbtype = '1';
        }
        if ($ntkcampaign == 'NTK') {
            $dbtype = '2';
        }

        $qaminimum = '100';
        $bcprefix = 'A';


        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => $dbtype,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 6, 1, intval($period_arr['year']))),
                'remark' => 'AutoUpload ' . date('Y-m-d H:i:s'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => $campaign_type,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );
            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    function make_campaignproducttrp($campaignname, $period, $type)
    {

        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        if ($type == 'trp') { ## TRP
            $campaign_product = '40';
            $campaign_type = '1';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }


        // var_dump($campaign_product);die();
        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 6, 1, intval($period_arr['year']))),
                'remark' => 'AutoUpload ' . date('Y-m-d H:i:s'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    function make_campaignproductxtmrw($campaignname, $period, $type)
    {

        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $tkcampaign = substr($campaignname, 9, 2);
        $ntkcampaign = substr($campaignname, 9, 3);

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        if ($type == 'xtmrw') { ## cop
            $campaign_product = '41';
            $campaign_type = '1';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }



        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 6, 1, intval($period_arr['year']))),
                'remark' => 'AutoUpload ' . date('Y-m-d H:i:s'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => $campaign_type,
                //'bcprefix'=> $bcprefix,
                //'qaminimum'=> $qaminimum
            );
            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    function create_autoupload_log($pinfo, $inserted, $dup, $uploadcode, $skip = "0")
    {
        $insert = array(
            'fullpath' => $pinfo['dirname'] . '/' . $pinfo['basename'],
            'filename' => $pinfo['basename'],
            'inserted' => $inserted,
            'dup' => $dup,
            'skip' => $skip,
            'uploadcode' => $uploadcode,
            'captanggal' => DATE('Y-m-d H:i:s')
        );
        $this->db->insert('tb_autoupload_log', $insert);
    }

    function get_autoupload_enginelist($cond = "")
    {
        $this->db->where('is_active', 1);
        if ($cond != "") {
            $this->db->where($cond, null, FALSE);
        }
        $qObj = $this->db->get('tb_autoupload_passph');
        $qArr = $qObj->num_rows() > 0 ? $qObj->result_array() : array();
        return $qArr;
    }

    function get_autoupload_enginelist_product($cond = "")
    {
        $this->db->where('is_active', 1);
        if ($cond != "") {
            $this->db->where($cond, null, FALSE);
        }
        $qObj = $this->db->get('tb_autoupload_passph_product');
        $qArr = $qObj->num_rows() > 0 ? $qObj->result_array() : array();
        return $qArr;
    }

    function mapping_trp($partner)
    {

        $inserted = 0;
        $inserted1 = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        ## Remove double prospect data ( for transaction on masterdata )
        $excelDataNoDup = $this->remove_samebasiccard_crp($qArr);

        foreach ($excelDataNoDup as $row1) {
            $insert = array();
            ## Main Data
            $insert['cif_no']              = $row1['field_3'];
            $insert['card_number_basic']   = $row1['field_4'];
            $insert['fullname']            = $row1['field_6'];
            $insert['max_loan']            = $row1['field_7'];
            $insert['card_exp']            = $row1['field_17'];
            $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_20']);
            ## Try to Fix DOB
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']             =  $this->convertTextDatefop($row1['field_20'], '/');
            }
            $insert['creditlimit']         = $row1['field_21'];

            ## Phone Number
            $insert['home_phone1_ori']     = $row1['field_22'];
            $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
            $insert['office_phone1_ori']   = $row1['field_23'];
            $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
            $insert['hp1_ori']             = $row1['field_24'];
            $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

            $insert['gender']              = $row1['field_25'];
            $insert['dummy_id']            = $row1['field_27'];
            $insert['status']              = $row1['field_28'];
            $insert['segment1']            = $row1['field_29'];
            $insert['datainfo']            = $row1['field_30'];
            $insert['custom2']             = $row1['field_32'];

            ## Default Data
            //$insert['id_tsm']              = $this->tsm_incharge;
            $insert['tgl_upload']          = DATE('Y-m-d');
            $insert['id_campaign']         = $row1['target_campaign'];
            $insert['uploadcode']          = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);

            array_push($insertData, $str);
            $inserted++;
        } ## End foreach

        foreach ($qArr as $row) {
            $trp = array();
            ##Crpdetail Data
            $trp['cif_no']              = $row['field_3'];
            $trp['crp_card']            = $row['field_4'];
            $trp['crp_cardtype']        = $row['field_5'];
            $trp['crp_amount']          = $row['field_7'];
            $trp['crp_cicilanrcp']      = $row['field_8'];
            $trp['crp_coreamount']      = $row['field_10'];
            $trp['crp_ippamount']       = $row['field_11'];
            $trp['crp_description']     = $row['field_12'];
            $trp['crp_detailipp']       = $row['field_13'];
            $trp['crp_interest']        = $row['field_14'];
            $trp['crp_fee']             = $row['field_15'];
            $trp['crp_countcard']       = $row['field_31'];
            $trp['tgl_upload']          = DATE('Y-m-d');
            $trp['id_campaign']         = $row['target_campaign'];
            $trp['uploadcode']          = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_crpdetail', $trp);

            array_push($insertData_dup, $str);
            $inserted1++;
        } ## End foreach
        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_dup, 500);
        $return['inserted'] = $inserted;
        $return['dup'] = 0;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_cp($partner)
    {
        $inserted = 0;
        $inserted1 = 0;
        $dup = 0;
        $skip = 0;
        $skip_reason = '';
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_cop');
        $qArr = $qObj->result_array();
        $qObj->free_result();

        ## Remove DUPLICATE prospect data 
        $excelDataNoDup = $this->remove_samebasiccard_acs($qArr, 'field_41'); ## Filter Cif number

        foreach ($excelDataNoDup as $row) {
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row['field_17'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
                $qObj->free_result();
            }
            //echo $this->db->last_query();  
            //var_dump($skip_reason);
            //die(); field
            $insert = array();
            if ($skip == 0) {
                ## Main Data
                $insert['fullname']            = $row['field_2'];
                $insert['social_number']       = $row['field_3'];
                $insert['pob']                 = $row['field_4'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_5']);
                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row['field_5'], '/');
                }
                $insert['home_address1']       = $row['field_6'];
                $insert['home_address2']       = $row['field_7'];
                $insert['gender']              = $row['field_8'];

                ## Phone Number
                $insert['home_phone1_ori']     = $row['field_15'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1_ori']   = $row['field_16'];
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1_ori']             = $row['field_17'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                $insert['email']               = $row['field_19'];
                $insert['code_tele']           = $row['field_22'];
                $insert['status']              = $row['field_23'];
                $insert['card_number_basic']   = $row['field_25'];
                $insert['card_type']           = $row['field_26'];
                $insert['creditlimit']         = $row['field_27'];
                //$insert['maiden_name']         = $row['field_28'];
                $insert['available_credit']    = $row['field_30'];
                $insert['card_exp']            = $row['field_31'];
                $insert['segment1']            = $row['field_35']; //$row['field_49'];
                $insert['segment2']            = $row['field_32']; //$row['field_40'];
                $insert['segment3']            = $row['field_43'];
                $insert['max_loan']            = $row['field_36'];
                $insert['loan1']               = $row['field_37'];
                $insert['loan2']               = $row['field_38'];
                $insert['loan3']               = $row['field_39'];
                $insert['cycle']               = $row['field_40'];
                $insert['cif_no']              = $row['field_41'];
                $insert['cnum']                = $row['field_46'];
                $insert['rdf']                 = $row['field_47'];
                $insert['datainfo']            = $row['field_48'];
                $insert['dummy_id']            = $row['field_50'];
                $insert['group_loan']          = $row['field_34'];

                ## Data for Verification
                $insert['bill_statement']      = $row['field_20'];
                $insert['autodebet']           = $row['field_21'];

                ## Default Data
                //$insert['id_tsm']           = $this->tsm_incharge;
                $insert['tgl_upload']       = DATE('Y-m-d');
                $insert['id_campaign']      = $row['target_campaign'];
                $insert['uploadcode']       = $uploadcode;
                $insert['skip_reason']       = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);

                array_push($insertData, $str);
                $inserted++;
            } else {
                ## Main Data
                $insert_skip['fullname']            = $row['field_2'];
                $insert_skip['social_number']       = $row['field_3'];
                $insert_skip['pob']                 = $row['field_4'];
                $insert_skip['dob']                 = $this->convertExcelToNormalDateTRP($row['field_5']);
                ## Try to Fix DOB
                if (substr($insert_skip['dob'], 0, 4) >= date('Y') || $insert_skip['dob'] == '0000-00-00') {
                    $insert_skip['dob']             =  $this->convertTextDatefop($row['field_5'], '/');
                }
                $insert_skip['home_address1']       = $row['field_6'];
                $insert_skip['home_address2']       = $row['field_7'];
                $insert_skip['gender']              = $row['field_8'];

                ## Phone Number
                $insert_skip['home_phone1_ori']     = $row['field_15'];
                $insert_skip['home_phone1']         = $this->phone_sensor->recognize($insert_skip['home_phone1_ori']);
                $insert_skip['office_phone1_ori']   = $row['field_16'];
                $insert_skip['office_phone1']       = $this->phone_sensor->recognize($insert_skip['office_phone1_ori']);
                $insert_skip['hp1_ori']             = $row['field_17'];
                $insert_skip['hp1']                 = $this->phone_sensor->recognize($insert_skip['hp1_ori']);

                $insert_skip['email']               = $row['field_19'];
                $insert_skip['code_tele']           = $row['field_22'];
                $insert_skip['status']              = $row['field_23'];
                $insert_skip['card_number_basic']   = $row['field_25'];
                $insert_skip['card_type']           = $row['field_26'];
                $insert_skip['creditlimit']         = $row['field_27'];
                //$insert_skip['maiden_name']         = $row['field_28'];
                $insert_skip['available_credit']    = $row['field_30'];
                $insert_skip['card_exp']            = $row['field_31'];
                $insert_skip['segment1']            = $row['field_35']; //$row['field_49'];
                $insert_skip['segment2']            = $row['field_32']; //$row['field_40'];
                $insert_skip['segment3']            = $row['field_26'];
                $insert_skip['max_loan']            = $row['field_36'];
                $insert_skip['loan1']               = $row['field_37'];
                $insert_skip['loan2']               = $row['field_38'];
                $insert_skip['loan3']               = $row['field_39'];
                $insert_skip['cycle']               = $row['field_40'];
                $insert_skip['cif_no']              = $row['field_41'];
                $insert_skip['cnum']                = $row['field_46'];
                $insert_skip['rdf']                 = $row['field_47'];
                $insert_skip['datainfo']            = $row['field_48'];
                $insert_skip['dummy_id']            = $row['field_50'];
                $insert_skip['group_loan']          = $row['field_34'];

                ## Data for Verification
                $insert_skip['bill_statement']      = $row['field_20'];
                $insert_skip['autodebet']           = $row['field_21'];

                ## Default Data
                //$insert_skip['id_tsm']           = $this->tsm_incharge;
                $insert_skip['tgl_upload']       = DATE('Y-m-d');
                $insert_skip['id_campaign']      = $row['target_campaign'];
                $insert_skip['uploadcode']       = $uploadcode;
                $insert_skip['skip_reason']       = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert_skip);

                array_push($insertData_skip, $str);
                $inserted1++;
            }
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $inserted1;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_tkp($partner)
    {
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            ## Main Data
            $insert['campaign_code']    = $row['field_2'];
            $insert['application_id']   = $row['field_3'];
            $insert['fullname']         = $row['field_4'];
            $insert['email']            = $row['field_5'];
            $insert['card_type']          = $row['field_8'];
            $insert['custom1']            = $this->convertExcelToNormalDate($row['field_10']); //createtime (tokopedia)
            $insert['custom2']            = $this->convertExcelToNormalDate($row['field_11']); //updatetime (tokopedia)
            $insert['home_city']          = $row['field_12'];

            $insert['dob']                = $this->convertExcelToNormalDate($row['field_13']);
            ## Try to Fix DOB
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']            =  $this->convertTextDate($row['field_13'], '/');
            }
            $insert['custom3']            = $row['field_14']; //income
            $insert['datainfo']           = $row['field_15']; //cc have
            $insert['social_number']      = clean_name($row['field_17']);
            $insert['gender']             = $row['field_20'];


            if ($row['field_26'] != '' || $row['field_27'] != '') {
                $insert['home_address1']  = $row['field_21'] . ' ' . 'RT' . ' ' . $row['field_26'] . ' ' . 'RW' . ' ' . $row['field_27'] . ' ' . $row['field_24']
                    . ' ' . $row['field_25'] . ' ' . $row['field_23'] . ' ' . $row['field_22'] . ' ' . $row['field_28'];
            }

            if ($row['field_26'] == '' || $row['field_27'] == '') {
                $insert['home_address1']  = $row['field_21'] . ' ' . $row['field_26'] . ' ' . $row['field_27'] . ' ' . $row['field_24']
                    . ' ' . $row['field_25'] . ' ' . $row['field_23'] . ' ' . $row['field_22'] . ' ' . $row['field_28'];
            }

            ## Phone Number
            $insert['hp1_ori']              = $row['field_6'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['cif_no']           = $insert['application_id'] . '/' . $partner . '_' . $insert['campaign_code'];
            $insert['datainfo']         = $insert['campaign_code'] . '/' . $insert['card_type'];
            $insert['id_tsm']           = $this->tsm_incharge;
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);
            array_push($insertData, $str);
            $inserted++;
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_bli($partner)
    {
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            ## Main Data
            $insert['campaign_code']    = $row['field_2'];
            $insert['application_id']   = $row['field_5'];
            $insert['card_type']        = $row['field_6'];
            $insert['fullname']         = $row['field_7'];
            $insert['email']            = $row['field_9'];
            $insert['home_city']        = $row['field_10'];
            $insert['gender']           = $row['field_11'];
            $insert['dob']              = $this->convertExcelToNormalDate($row['field_12']);
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']          =  $this->convertTextDate($row['field_12'], '/');
            }
            $insert['social_number']    = clean_name($row['field_13']);
            $insert['custom1']          = $this->convertExcelToNormalDate($row['field_3']); //createtime (blibli)
            $insert['bidang_usaha']     = $row['field_14'];

            ## Phone Number
            $insert['hp1_ori']              = $row['field_8'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['cif_no']           = $insert['application_id'] . '/' . $partner . '_' . $insert['campaign_code'];
            $insert['datainfo']         = $insert['campaign_code'] . '/' . $insert['card_type'];
            $insert['id_tsm']           = $this->tsm_incharge;
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);
            array_push($insertData, $str);
            $inserted++;
        } ## End foreach


        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_map($partner)
    {
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            ## Main Data
            $insert['campaign_code']    = $row['field_2'];
            $insert['application_id']   = $row['field_5'];
            $insert['card_type']        = $row['field_6'];
            $insert['fullname']         = $row['field_7'];
            $insert['email']            = $row['field_9'];
            $insert['home_city']        = $row['field_10'];
            $insert['gender']           = $row['field_11'];
            $insert['dob']              = $this->convertExcelToNormalDate($row['field_12']);
            ## Try to Fix DOB
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']            =  $this->convertTextDate($row['field_12'], '/');
            }
            $insert['social_number']    = clean_name($row['field_13']);
            $insert['custom1']          = $row['field_3']; //createtime (blibli)
            $insert['bidang_usaha']     = $row['field_14'];

            ## Phone Number
            $insert['hp1_ori']              = $row['field_8'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['cif_no']           = $insert['application_id'] . '/' . $partner . '_' . $insert['campaign_code'];
            $insert['datainfo']         = $insert['campaign_code'] . '/' . $insert['card_type'];
            $insert['id_tsm']           = $this->tsm_incharge;
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);
            array_push($insertData, $str);
            $inserted++;
        } ## End foreach


        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_gld($partner)
    {
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            ## Main Data
            $insert['custom1']          = $this->convertExcelToNormalDate($row['field_2']); //Leads Date (Golden Rama)
            $insert['campaign_code']    = $row['field_3'];
            $insert['application_id']   = $row['field_4'];
            $insert['card_type']        = $row['field_5'];
            $insert['fullname']         = $row['field_6'];
            $insert['email']            = $row['field_8'];
            $insert['home_city']        = $row['field_9'];
            $insert['gender']           = $row['field_10'];
            $insert['dob']              = $this->convertExcelToNormalDate($row['field_11']);
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']          =  $this->convertTextDate($row['field_11'], '/');
            }
            $insert['social_number']    = clean_name($row['field_12']);
            $insert['bidang_usaha']     = $row['field_13'];

            ## Phone Number
            $insert['hp1_ori']              = $row['field_7'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['cif_no']           = $insert['application_id'] . '/' . $partner . '_' . $insert['campaign_code'];
            $insert['datainfo']         = $insert['campaign_code'] . '/' . $insert['card_type'];
            $insert['id_tsm']           = $this->tsm_incharge;
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);
            array_push($insertData, $str);
            $inserted++;
        } ## End foreach


        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_shp($partner)
    {
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            ## Main Data
            $insert['campaign_code']    = $row['field_3'];
            $insert['application_id']   = $row['field_4'];
            $insert['card_type']        = $row['field_5'];
            $insert['fullname']         = $row['field_6'];
            $insert['email']            = $row['field_8'];
            $insert['home_city']        = $row['field_9'];
            $insert['gender']           = $row['field_10'];
            $insert['dob']              = $this->convertExcelToNormalDate($row['field_11']);
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']          =  $this->convertTextDate($row['field_11'], '/');
            }
            $insert['social_number']    = clean_name($row['field_12']);
            $insert['custom1']          = $this->convertExcelToNormalDate($row['field_2']); //createtime (blibli)
            $insert['bidang_usaha']     = $row['field_13'];

            ## Phone Number
            $insert['hp1_ori']              = $row['field_7'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['cif_no']           = $insert['application_id'] . '/' . $partner . '_' . $insert['campaign_code'];
            $insert['datainfo']         = $insert['campaign_code'] . '/' . $insert['card_type'];
            $insert['id_tsm']           = $this->tsm_incharge;
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);
            array_push($insertData, $str);
            $inserted++;
        } ## End foreach


        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_anw($partner)
    {
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            ## Main Data
            $insert['application_id']   = $row['field_1'];
            $insert['card_type']        = $row['field_2'];
            $insert['fullname']         = $row['field_4'];
            $insert['gender']           = $row['field_5'];
            $insert['dob']              = $this->convertExcelToNormalDate($row['field_6']);
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']          =  $this->convertTextDate($row['field_6'], '/');
            }
            $insert['social_number']    = clean_name($row['field_7']);

            $insert['home_city']        = $row['field_16'];
            $insert['bidang_usaha']     = $row['field_27'];
            $insert['email']            = $row['field_47'];
            $insert['campaign_code']    = $row['field_62'];
            $insert['custom1']          = $this->convertExcelToNormalDate($row['field_52']); // tgl_lead

            ## Phone Number
            $insert['hp1_ori']              = $row['field_14'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['cif_no']           = $insert['application_id'] . '/' . $partner . '_' . $insert['campaign_code'];
            $insert['datainfo']         = $insert['campaign_code'] . '/' . $insert['card_type'];
            $insert['id_tsm']           = $this->tsm_incharge;
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);
            array_push($insertData, $str);
            $inserted++;
        } ## End foreach


        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_atk($partner)
    {
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            ## Main Data
            $insert['application_id']   = $row['field_1'];
            $insert['card_type']        = $row['field_2'];
            $insert['fullname']         = $row['field_4'];
            $insert['gender']           = $row['field_5'];
            $insert['dob']              = $this->convertExcelToNormalDate($row['field_6']);
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']          =  $this->convertTextDate($row['field_6'], '/');
            }
            $insert['social_number']    = clean_name($row['field_7']);

            $insert['home_city']        = $row['field_16'];
            $insert['bidang_usaha']     = $row['field_27'];
            $insert['email']            = $row['field_47'];
            $insert['campaign_code']    = $row['field_62'];
            $insert['custom1']          = $this->convertExcelToNormalDate($row['field_52']); // tgl_lead

            ## Phone Number
            $insert['hp1_ori']              = $row['field_14'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['cif_no']           = $insert['application_id'] . '/' . $partner . '_' . $insert['campaign_code'];
            $insert['datainfo']         = $insert['campaign_code'] . '/' . $insert['card_type'];
            $insert['id_tsm']           = $this->tsm_incharge;
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);
            array_push($insertData, $str);
            $inserted++;
        } ## End foreach


        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_apn($partner)
    {
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            ## Main Data
            $insert['application_id']   = $row['field_1'];
            $insert['card_type']        = $row['field_2'];
            $insert['fullname']         = $row['field_4'];
            $insert['gender']           = $row['field_5'];
            $insert['dob']              = $this->convertExcelToNormalDate($row['field_6']);
            if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                $insert['dob']          =  $this->convertTextDate($row['field_6'], '/');
            }
            $insert['social_number']    = clean_name($row['field_7']);

            $insert['home_city']        = $row['field_16'];
            $insert['bidang_usaha']     = $row['field_27'];
            $insert['email']            = $row['field_47'];
            $insert['campaign_code']    = $row['field_62'];
            $insert['custom1']          = $this->convertExcelToNormalDate($row['field_52']);

            ## Phone Number
            $insert['hp1_ori']              = $row['field_14'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['cif_no']           = $insert['application_id'] . '/' . $partner . '_' . $insert['campaign_code'];
            $insert['datainfo']         = $insert['campaign_code'] . '/' . $insert['card_type'];
            $insert['id_tsm']           = $this->tsm_incharge;
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_prospect', $insert);
            array_push($insertData, $str);
            $inserted++;
        } ## End foreach


        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function mapping_xtmrw($partner)
    {
        $inserted = 0;
        $inserted1 = 0;
        $dup = 0;
        //$skip = 0;
        //$skip_reason = '';
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();


        foreach ($qArr as $row) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row['field_10'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                //echo $this->db->last_query();  
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }
            //echo $this->db->last_query();  
            //var_dump($skip_reason);
            //die();
            $insert = array();
            if ($skip == 0) {
                ## Main Data
                $insert['cif_no']              = $row['field_1'];
                $insert['cnum']                = $row['field_2'];
                $insert['no_reff']             = $row['field_4'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_6']);
                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row['field_6'], '/');
                }
                $insert['fullname']            = $row['field_8'];

                ## Phone Number
                $insert['hp1_ori']             = $row['field_10'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                $insert['gender']              = $row['field_11'];
                $insert['cabang']              = $row['field_12'];
                $insert['code_tele']           = $row['field_13'];
                $insert['datainfo']            = $row['field_14'];

                ## Default Data
                //$insert['id_tsm']           = $this->tsm_incharge;
                $insert['tgl_upload']       = DATE('Y-m-d');
                $insert['id_tsm']           = $this->tsm_incharge;
                $insert['id_campaign']      = $row['target_campaign'];
                $insert['uploadcode']       = $uploadcode;
                $insert['skip_reason']       = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);

                array_push($insertData, $str);
                $inserted++;
            } else {
                ## Main Data
                $insert['cif_no']              = $row['field_1'];
                $insert['cnum']                = $row['field_2'];
                $insert['no_reff']             = $row['field_4'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_6']);
                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row['field_6'], '/');
                }
                $insert['fullname']            = $row['field_8'];

                ## Phone Number
                $insert['hp1_ori']             = $row['field_10'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                $insert['gender']              = $row['field_11'];
                $insert['cabang']              = $row['field_12'];
                $insert['code_tele']           = $row['field_13'];
                $insert['datainfo']            = $row['field_14'];

                ## Default Data
                //$insert['id_tsm']           = $this->tsm_incharge;
                $insert['tgl_upload']       = DATE('Y-m-d');
                $insert['id_tsm']           = $this->tsm_incharge;
                $insert['id_campaign']      = $row['target_campaign'];
                $insert['uploadcode']       = $uploadcode;
                $insert['skip_reason']       = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);

                array_push($insertData_skip, $str);
                $inserted1++;
            }
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }


    public function uploadExcel($file_path = '')
    {

        ##Load PHPExcel Plugin
        $this->load->library("PHPExcel");
        ##################################

        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(false);
        $objPHPExcel = $objReader->load($file_path);

        $max_column = 100;
        ## BUG	$endRow = $objPHPExcel->getActiveSheet()->getHighestRow() > 1000 ? 1000 : $objPHPExcel->getActiveSheet()->getHighestRow();
        $endRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $endColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $endColumn = PHPExcel_Cell::columnIndexFromString($endColumn) > $max_column ? $max_column : PHPExcel_Cell::columnIndexFromString($endColumn);

        $insert_count = 0;
        $until_row = $endRow;
        $idx = 0;
        $err_line = array();

        for ($row = 1; $row <= $until_row; $row++) {

            ##check dob
            //				$validation = array(
            //				 'dob'=> $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3, $row)->getValue()
            //				);

            //$is_valid = $this->doValidation($validation);
            $is_valid = 1;

            if ($is_valid == 0) {
                array_push($err_line, $row);
                continue;
            }


            ## Try Get All Column
            $maxcolumn = $max_column;
            for ($i = 1; $i <= $maxcolumn; $i++) {
                $data[$idx]['field_' . $i] = TRIM($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i - 1, $row)->getValue());
            }

            $idx++;
        }
        //var_dump($data);die();
        $return['excelData'] = $data;
        $return['errLine'] = $err_line;
        return $return;
    }

    public function uploadExcelAuto($file_path = '')
    {

        ##Load PHPExcel Plugin
        $this->load->library("PHPExcel");
        ##################################
        
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(false);
        $objPHPExcel = $objReader->load($file_path);
        
        $max_column = 100;
        // debug mra
        // var_dump($objPHPExcel);die();
        $endRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $endColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $endColumn = PHPExcel_Cell::columnIndexFromString($endColumn) > $max_column ? $max_column : PHPExcel_Cell::columnIndexFromString($endColumn);

        $insert_count = 0;
        $until_row = $endRow;
        $idx = 0;
        $err_line = array();

        for ($row = 1; $row <= $until_row; $row++) {

            ##check dob
            //				$validation = array(
            //				 'dob'=> $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3, $row)->getValue()
            //				);

            //$is_valid = $this->doValidation($validation);
            $is_valid = 1;

            if ($is_valid == 0) {
                array_push($err_line, $row);
                continue;
            }


            ## Try Get All Column
            $maxcolumn = $max_column;
            for ($i = 1; $i <= $maxcolumn; $i++) {
                $data[$idx]['field_' . $i] = TRIM($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i - 1, $row)->getValue());
            }

            $idx++;
        }
        //var_dump($data);die();
        $return['excelData'] = $data;
        $return['errLine'] = $err_line;
        return $return;
    }

    function dup_logic2nd($cnum, $source)
    {
        $is_dup = 0;
        $ref = "";
        $month = 3; // jumlah bulan kebelakang untuk dicek

        $range_start = DATE('Y-m-d', mktime(0, 0, 0, (DATE('m') - $month), 1, DATE('Y')));
        $range_end = DATE('Y-m-d');
        $this->db->select('id_prospect');
        $this->db->where('cnum', $cnum);
        $this->db->where('plafon24', $source);
        $this->db->where("tgl_upload BETWEEN '$range_start' AND '$range_end'", NULL, FALSE);
        $qObj = $this->db->get('tb_prospect');
        $qArr = $qObj->num_rows() > 0 ? $qObj->row_array() : array();
        if (count($qArr) > 0) {
            $is_dup = 1;
            $ref = $qArr['id_prospect'];
        }
        return array('is_dup' => $is_dup, 'ref' => $ref);
    }

    function convertToExcelDate($date)
    {
        $stop = unixtojd(strtotime($date));
        $start = gregoriantojd(1, 1, 1900);
        return ($stop - $start) + 2;
    }

    function convertExcelToNormalDateTRP($date)
    {
        //var_dump($sepatator);die();
        if ($date != '') {
            $get_tgl = substr($date, 0, 2);
            $get_bln = substr($date, 2, 2);
            $get_thn = substr($date, 4, 4);
            //var_dump($get_tgl);die();
            return date('Y-m-d', strtotime($get_thn . '-' . $get_bln . '-' . $get_tgl));
        } else {
            return '';
        }
    }

    function convertExcelToNormalDate($date)
    {

        $day_difference = 25569; //Day difference between 1 January 1900 to 1 January 1970
        $day_to_seconds = 86400; // no. of seconds in a day
        $unixtime = ($date - $day_difference) * $day_to_seconds;
        return date('Y-m-d', $unixtime);
    }

    function convertTextDate($datestr, $sepatator = "-")
    {
        if ($datestr != '') {
            $chunk = explode($sepatator, $datestr);

            $get_tgl = str_pad($chunk[0], 2, '0', STR_PAD_LEFT);
            $get_bln = str_pad($chunk[1], 2, '0', STR_PAD_LEFT);
            $get_thn = $chunk[2];
            return date('Y-m-d', strtotime($get_tgl . '-' . $get_bln . '-' . $get_thn));
        } else {
            return '';
        }
    }

    function convertTextDatefop($datestr)
    {
        if ($datestr != '') {
            $sepatator = '-';
            $get_tgl = substr($datestr, 0, 2);
            $get_bln = substr($datestr, 2, 2);
            $get_thn = substr($datestr, 4, 4);
            return date('Y-m-d', strtotime($get_tgl . '-' . $get_bln . '-' . $get_thn));
        } else {
            return '';
        }
    }

    function multiple_insert($insertData, $max_insert = 500)
    {
        ## Speedup Insert Tweak
        $sqlBag = array(
            'SET unique_checks=0',
            'SET foreign_key_checks=0',
            'SET autocommit=0',
            'SET GLOBAL sync_binlog=1000',
            'SET GLOBAL innodb_flush_log_at_trx_commit=0',
	    'SET long_query_time = 99999'
        );
        foreach ($sqlBag as $sql) {
            $this->db->query($sql);
        }

        $finalquery = "";
        $inserted = 0;
        $datacount = count($insertData);

        if ($datacount > 0) {
            ## Find insert Destination;
            $full_str = $insertData[0];
            $cut_string_pos = strpos($full_str, 'VALUES');
            $prefix = "\n" . substr($full_str, 0, $cut_string_pos) . ' VALUES ' . "\n";

            if ($datacount <= $max_insert) { ## Karna Datanya kurang dari max insert, langsung 1 query aja;
                $finalquery .= $prefix;
                $i = 0;
                $j = COUNT($insertData);
                foreach ($insertData as $sql) {
                    $edd = substr($sql, $cut_string_pos + 6);
                    $i++;
                    if ($i < $j) {
                        $finalquery .= $edd . "," . "\n";
                    } else {
                        $finalquery .= $edd . "\n";
                    }
                }
                $this->db->query($finalquery); //insert here;
                $aff = $this->db->affected_rows();
                $inserted += $aff;
            } else { ## Query berulang karena datanya lebih dari max insert
                $insertChunk = array_chunk($insertData, $max_insert);
                //var_dump($insertChunk);
                foreach ($insertChunk as $chunk) {
                    $finalquery .= $prefix;
                    $i = 0;
                    $j = COUNT($chunk);
                    foreach ($chunk as $sql) {
                        $edd = substr($sql, $cut_string_pos + 6);
                        $i++;
                        if ($i < $j) {
                            $finalquery .= $edd . "," . "\n";
                        } else {
                            $finalquery .= $edd . "\n";
                        }
                    }
                    $this->db->query($finalquery); //insert here;
                    $aff = $this->db->affected_rows();
                    $inserted += $aff;
                    $finalquery = "";
                }
            }
        }

        ## Revert Speedup Insert Tweak
        $sqlBag = array(
            'SET unique_checks=1',
            'SET foreign_key_checks=1',
            'SET autocommit=1',
            'SET GLOBAL sync_binlog=300',
            'SET GLOBAL innodb_flush_log_at_trx_commit=2',
	    'SET long_query_time = 3'
        );
        foreach ($sqlBag as $sql) {
            $this->db->query($sql);
        }

        return $inserted;
    }

    function remove_samebasiccard_crp($excelData)
    {
        $idx = 0;
        $cardBag = array();
        foreach ($excelData as $excelRow) {
            $card = $excelRow['field_27'];
            if (!empty($card)) {
                $is_samecard = in_array($card, $cardBag, true);
                if ($is_samecard) {
                    unset($excelData[$idx]); //remove from array;
                } else {
                    array_push($cardBag, $card);
                }
            }
            $idx++;
        }
        return array_values($excelData); ## return with reindexed array; 
    }

    ############################# FOP #############################################################
    function remove_samebasiccard_fop($excelData)
    {
        $idx = 0;
        $cardBag = array();
        foreach ($excelData as $excelRow) {
            $card = $excelRow['field_53'];
            if (!empty($card)) {
                $is_samecard = in_array($card, $cardBag, true);
                if ($is_samecard) {
                    unset($excelData[$idx]); //remove from array;
                } else {
                    array_push($cardBag, $card);
                }
            }
            $idx++;
        }
        return array_values($excelData); ## return with reindexed array; 
    }

    function make_campaignproductfop($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');
        //echo $qObj->row_array()['id_campaign'];

        if ($type == 'fp') { ## FOP
            $campaign_product = '44';
            $campaign_type = '1';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }

        $insert_id = '';

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']) + 13, intval($period_arr['year']))),
                'remark' => 'FOP STMT ' . date('M Y'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
            $insert_id = $this->db->insert_id();
        } else {
            $insert_id = $qObj->row_array()['id_campaign'];
        }
        return $insert_id;
    }

    function autoupload_byengineproduct_fop($excelData, $period, $engine, $namcampaign)
    {
        $enginenumber = 6;
        $namcampaign = preg_replace('/\s+/', ' ', $namcampaign);
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_fop');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_fop', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_fop
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT target_campaign as campaign FROM tb_uploadpreview_fop limit 1
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $id_campaign = $this->make_campaignproductfop($campaigns['campaign'], $period,  $type = 'fp');
        }
        // var_dump($id_campaign);
        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_fop SET target_campaign = {$id_campaign}";
        // LEFT JOIN tb_campaign ON tb_uploadpreview_fop.target_campaign = tb_campaign.name
        //  SET tb_uploadpreview_fop.target_campaign = tb_campaign.id_campaign
        // ";

        $this->db->simple_query($sql);

        ## Start mapping to real table
        // $inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'fp':
                $res = $this->mapping_fop($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function mapping_fop($partner)
    {
        $inserted = 0;
        $dup = 0;
        $totalSkip = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_trx = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_fop');
        $qArr = $qObj->result_array();

        ## Remove double prospect data ( for transaction on masterdata )
        $excelDataNoDup = $this->remove_samebasiccard_fop($qArr);

        foreach ($excelDataNoDup as $row1) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row1['field_17'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }

            if ($skip == 0) {
                ## Main Data
                $insert['gender']              = $row1['field_8'];
                $insert['cif_no']              = $row1['field_42'];
                $insert['card_number_basic']   = $row1['field_25'];
                $insert['fullname']            = $row1['field_2'];
                $insert['pob']                 = $row1['field_4'];
                $insert['email']               = $row1['field_19'];
                $insert['code_tele']           = $row1['field_22'];
                $insert['status']              = $row1['field_23'];
                $insert['card_number_basic']   = $row1['field_25'];
                $insert['card_type']           = $row1['field_26'];
                $insert['creditlimit']         = $row1['field_27'];
                $insert['maiden_name']         = $row1['field_28'];
                $insert['available_credit']    = $row1['field_30'];
                $insert['card_exp']            = $row1['field_31'];
                $insert['cycle']               = $row1['field_41'];
                $insert['max_loan']            = $row1['field_45'];
                $insert['cnum']                = $row1['field_49'];
                $insert['datainfo']            = $row1['field_51'];
                $insert['segment1']            = $row1['field_52'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_5']);
                $insert['dummy_id']            = $row1['field_53'];
                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row1['field_5'], '/');
                }

                ## Phone Number
                $insert['home_phone1_ori']     = $row1['field_15'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1_ori']   = $row1['field_16'];
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1_ori']             = $row1['field_17'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Default Data
                $insert['tgl_upload']          = DATE('Y-m-d');
                $insert['id_campaign']         = $row1['target_campaign'];
                $insert['uploadcode']          = $uploadcode;

                ## Data for Verification
                $insert['bill_statement']      = $row1['field_20'];
                $insert['autodebet']           = $row1['field_24'];

                ## Data Group_Loan
                $insert['group_loan']          = $row1['field_34'];

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);

                array_push($insertData, $str);
                $inserted++;

                //var_dump($insertData);echo "<br>";

            } else {
                ## Main Data
                $insert['gender']              = $row1['field_8'];
                $insert['cif_no']              = $row1['field_42'];
                $insert['card_number_basic']   = $row1['field_25'];
                $insert['fullname']            = $row1['field_2'];
                $insert['pob']                 = $row1['field_4'];
                $insert['email']               = $row1['field_19'];
                $insert['code_tele']           = $row1['field_22'];
                $insert['status']              = $row1['field_23'];
                $insert['card_number_basic']   = $row1['field_25'];
                $insert['card_type']           = $row1['field_26'];
                $insert['creditlimit']         = $row1['field_27'];
                $insert['maiden_name']         = $row1['field_28'];
                $insert['available_credit']    = $row1['field_30'];
                $insert['card_exp']            = $row1['field_31'];
                $insert['cycle']               = $row1['field_41'];
                $insert['max_loan']            = $row1['field_45'];
                $insert['cnum']                = $row1['field_49'];
                $insert['datainfo']            = $row1['field_51'];
                $insert['segment1']            = $row1['field_52'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_5']);
                $insert['dummy_id']            = $row1['field_53'];
                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row1['field_5'], '/');
                }

                ## Phone Number
                $insert['home_phone1_ori']     = $row1['field_15'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1_ori']   = $row1['field_16'];
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1_ori']             = $row1['field_17'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Default Data
                $insert['tgl_upload']          = DATE('Y-m-d');
                $insert['id_campaign']         = $row1['target_campaign'];
                $insert['uploadcode']          = $uploadcode;

                ## Data for Verification
                $insert['bill_statement']      = $row1['field_20'];
                $insert['autodebet']           = $row1['field_24'];

                ## Data Group_Loan
                $insert['group_loan']          = $row1['field_34'];

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);

                array_push($insertData_skip, $str);
                $totalSkip++;
            }
            //  $inserted++;
        } ## End foreach

        $trxInsert = 0; ## declare jumlah insert trx 
        foreach ($qArr as $row) {
            $fop = array();
            ##FOP Data
            $fop['cif_no']              = $row['field_42'];
            $fop['cnum']                = $row['field_49'];
            $fop['id_campaign']         = $row['target_campaign'];
            $fop['trx_card']            = $row['field_10'];
            $fop['trx_cardtype']        = $row['field_54'];
            $fop['trx_reff']            = $row['field_21'];
            $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_40']);
            $fop['trx_amount']          = $row['field_36'];
            $fop['trx_description']     = $row['field_39'];
            $fop['trx_countcard']       = $row['field_50'];
            $fop['uploadcode']          = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_trxdetail', $fop);

            array_push($insertData_trx, $str);
            $trxInsert++;
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_trx, 500);
        $this->multiple_insert($insertData_skip, 500);
        $return['inserted'] = $inserted;
        $return['trxInsert'] = $trxInsert;
        $return['dup'] = $totalSkip;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    #################################### ACS ###########################################################
    function autoupload_byengineproduct_acs($excelData, $period, $engine, $namcampaign, $type)
    {
        $enginenumber = 7;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_act');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_act', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_act
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_act
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproductacs($campaigns['campaign'], $period, $type);
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_act
                LEFT JOIN tb_campaign ON tb_uploadpreview_act.target_campaign = tb_campaign.name
                SET tb_uploadpreview_act.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);

        ## Start mapping to real table
        switch ($engine['engine']) {
            case 'acs':
                $res = $this->mapping_acs($engine['partner'], '48');
                break;
            case 'bp':
                $res = $this->mapping_acs($engine['patner'], '36');
                break;
            case 'act':
                $res = $this->mapping_acs($engine['partner'], '50');
                break;
            case 'act i8':
                $res = $this->mapping_acs($engine['partner'], '50');
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function autoupload_byengineproductcptmrwold($excelData, $period, $engine, $namcampaign)
    {

        $enginenumber = 3;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproduct($campaigns['campaign'], $period,  $type = 'cop');
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview.target_campaign = tb_campaign.name
                SET tb_uploadpreview.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## Start mapping to real table
        //$inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'cp':
                $res = $this->mapping_cptmrw($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function autoupload_byengineproductcptmrw($excelData, $period, $engine, $namcampaign)
    {

        $enginenumber = 3;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        $namecampaign = 'COP TMRW';

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_cop_tmrw');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_cop_tmrw', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_cop_tmrw
            SET target_campaign = CONCAT(UPPER('{$namecampaign}'), ' ', '{$period_month}', ' ','{$period_year}')
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_cop_tmrw
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproduct($campaigns['campaign'], $period,  $type = 'cop');
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_cop_tmrw
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview_cop_tmrw.target_campaign = tb_campaign.name
                SET tb_uploadpreview_cop_tmrw.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);
        //die($sql);
        ## Start mapping to real table
        //$inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'cp':
                $res = $this->mapping_cptmrw($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function mapping_cptmrw($partner)
    {
        $inserted = 0;
        $inserted1 = 0;
        $dup = 0;
        $skip = 0;
        $skip_reason = '';
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_cop_tmrw');
        $qArr = $qObj->result_array();
        $qObj->free_result();

        foreach ($qArr as $row) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row['field_6'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
                $qObj->free_result();
            }
            //echo $this->db->last_query();  
            //var_dump($skip_reason);
            //die();
            if ($skip == 0) {
                ## Main Data
                $insert['fullname']            = $row['field_2'];
                //$insert['social_number']       = $row['field_3'];
                //$insert['pob']                 = $row['field_4'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_3']);
                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row['field_3'], '/');
                }
                //$insert['home_address1']       = $row['field_6'];
                //$insert['home_address2']       = $row['field_7'];
                $insert['gender']              = $row['field_4'];

                ## Phone Number
                //$insert['home_phone1_ori']     = $row['field_15'];
                //$insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1_ori']   = $row['field_5'];
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1_ori']             = $row['field_6'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                $insert['status']              = $row['field_7'];
                $insert['card_number_basic']   = $row['field_8'];
                $insert['card_type']           = $row['field_9'];
                $insert['creditlimit']         = $row['field_10'];
                $insert['available_credit']    = $row['field_11'];
                $insert['card_exp']            = $row['field_12'];
                $insert['max_loan']            = $row['field_14'];
                $insert['loan1']               = $row['field_15'];
                $insert['loan2']               = $row['field_16'];
                $insert['loan3']               = $row['field_17'];
                $insert['cycle']               = $row['field_18'];
                $insert['cif_no']              = $row['field_19'];
                $insert['cnum']                = $row['field_20'];
                $insert['rdf']                 = $row['field_21'];
                $insert['datainfo']            = $row['field_22'];
                $insert['segment1']            = $row['field_23'];
                $insert['segment2']            = $row['field_29'];
                $insert['segment3']            = $row['field_30'];
                $insert['dummy_id']            = $row['field_24'];

                // $insert['no_reff']             = $row['field_32'];
                // $insert['income']              = $row['field_33'];
                // $insert['asn']                 = $row['field_32'];

                ## Mapping Verification
                $insert['autodebet']           = $row['field_27'];

                ## Default Data
                //$insert['id_tsm']           = $this->tsm_incharge;
                $insert['tgl_upload']       = DATE('Y-m-d');
                $insert['id_campaign']      = $row['target_campaign'];
                $insert['uploadcode']       = $uploadcode;
                $insert['skip_reason']       = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);

                array_push($insertData, $str);
                $inserted++;
            } else {
                ## Main Data
                $insert_skip['fullname']            = $row['field_2'];
                //$insert_skip['social_number']       = $row['field_3'];
                //$insert_skip['pob']                 = $row['field_4'];
                $insert_skip['dob']                 = $this->convertExcelToNormalDateTRP($row['field_3']);
                ## Try to Fix DOB
                if (substr($insert_skip['dob'], 0, 4) >= date('Y') || $insert_skip['dob'] == '0000-00-00') {
                    $insert_skip['dob']             =  $this->convertTextDatefop($row['field_3'], '/');
                }
                //$insert_skip['home_address1']       = $row['field_6'];
                //$insert_skip['home_address2']       = $row['field_7'];
                $insert_skip['gender']              = $row['field_4'];

                ## Phone Number
                //$insert_skip['home_phone1_ori']     = $row['field_15'];
                //$insert_skip['home_phone1']         = $this->phone_sensor->recognize($insert_skip['home_phone1_ori']);
                $insert_skip['office_phone1_ori']   = $row['field_5'];
                $insert_skip['office_phone1']       = $this->phone_sensor->recognize($insert_skip['office_phone1_ori']);
                $insert_skip['hp1_ori']             = $row['field_6'];
                $insert_skip['hp1']                 = $this->phone_sensor->recognize($insert_skip['hp1_ori']);

                $insert_skip['status']              = $row['field_7'];
                $insert_skip['card_number_basic']   = $row['field_8'];
                $insert_skip['card_type']           = $row['field_9'];
                $insert_skip['creditlimit']         = $row['field_10'];
                $insert_skip['available_credit']    = $row['field_11'];
                $insert_skip['card_exp']            = $row['field_12'];
                $insert_skip['max_loan']            = $row['field_14'];
                $insert_skip['loan1']               = $row['field_15'];
                $insert_skip['loan2']               = $row['field_16'];
                $insert_skip['loan3']               = $row['field_17'];
                $insert_skip['cycle']               = $row['field_18'];
                $insert_skip['cif_no']              = $row['field_19'];
                $insert_skip['cnum']                = $row['field_20'];
                $insert_skip['rdf']                 = $row['field_21'];
                $insert_skip['datainfo']            = $row['field_22'];
                $insert_skip['segment1']            = $row['field_23'];
                $insert_skip['segment2']            = $row['field_29'];
                $insert_skip['segment3']            = $row['field_30'];
                $insert_skip['dummy_id']            = $row['field_24'];

                $insert_skip['no_reff']             = $row['field_32'];
                $insert_skip['income']              = $row['field_33'];
                $insert_skip['asn']                 = $row['field_32'];

                ## Mapping Verification
                $insert_skip['autodebet']           = $row['field_27'];

                ## Default Data
                //$insert_skip['id_tsm']           = $this->tsm_incharge;
                $insert_skip['tgl_upload']       = DATE('Y-m-d');
                $insert_skip['id_campaign']      = $row['target_campaign'];
                $insert_skip['uploadcode']       = $uploadcode;
                $insert_skip['skip_reason']       = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert_skip);

                array_push($insertData_skip, $str);
                $inserted1++;
            }
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $inserted1;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }


    function make_campaignproductacs($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        if ($type == 'acs') { ## ACS
            $campaign_product = '48';
            $campaign_type = '1';
        } elseif ($type == 'bp') {
            $campaign_product = '36';
            $campaign_type = '1';
        } elseif ($type == 'act' || $type == 'act i8') { ## ACT
            $campaign_product = '50';
            $campaign_type = '1';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']) + 7, intval($period_arr['year']))),
                'remark' => ucwords($type) . ' ' . date('M Y'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    function mapping_acs($partner, $id_product = '')
    {
        $inserted = 0;
        $inserted1 = 0;
        $inserted_skip = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_xsell = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_act');
        $qArr = $qObj->result_array();

        ## Remove double prospect data ( for transaction on masterdata )
        if ($partner != 'act i8') {
            $excelDataNoDup = $this->remove_samebasiccard_acs($qArr, 'field_40'); ## data & lokasi field ACS & ACT
        } else {
            $excelDataNoDup = $this->remove_samebasiccard_acs($qArr, 'field_37'); ## data & lokasi field ACT I8            
        }

        if ($partner != 'act i8') {
            foreach ($excelDataNoDup as $row1) {
                $insert = array();
                $skip = 0;
                $skip_reason = '';
                $hp1_ori            = $row1['field_17'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                // Update Enddate Campaign
                $expiredcamp = $qArr[0]['field_64'];
                $camp_target = $qArr[0]['target_campaign'];
                $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
                $satu = $this->update_campaign($expiredcamp2, $camp_target);
                $this->db->simple_query($satu);
                // End Update Campaign Enddate

                if ($skip == 0) {
                    ## Main Data ACS & ACT
                    $insert['fullname']            = $row1['field_2'];
                    $insert['social_number']       = $row1['field_3'];
                    $insert['pob']                 = $row1['field_4'];
                    $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_5']);
                    $insert['email']               = $row1['field_19'];
                    $insert['card_number_basic']   = $row1['field_25'];
                    $insert['card_type']           = $row1['field_26'];
                    $insert['creditlimit']         = $row1['field_27'];
                    $insert['card_exp']            = $row1['field_31'];
                    $insert['cif_no']              = $row1['field_40'];
                    $insert['gender']              = $row1['field_52'];
                    $insert['cnum']                = $row1['field_65'];
                    $insert['segment1']            = $row1['field_68'];
                    $insert['dummy_id']            = $row1['field_70'];
                    $insert['segment2']            = $row1['field_71'];
                    $insert['remarks']             = $row1['field_73'];
                    $insert['mgm_id']             = $row1['field_74'];

                    ## Try to Fix DOB
                    if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                        $insert['dob']             =  $this->convertTextDatefop($row1['field_5'], '/');
                    }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row1['field_15'];
                    $insert['office_phone1_ori']   = $row1['field_16'];
                    $insert['hp1_ori']             = $row1['field_17'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Default Data
                    $insert['tgl_upload']          = DATE('Y-m-d');
                    $insert['id_campaign']         = $row1['target_campaign'];
                    $insert['uploadcode']          = $uploadcode;

                    ## Data for Verification
                    $insert['bill_statement']      = $row1['field_20'];
                    $insert['autodebet']           = $row1['field_21'];

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);
                    // var_dump($str);
                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $acs['cif_no']              = $row1['field_40'];
                    $acs['id_campaign']         = $row1['target_campaign'];
                    $acs['id_product']          = $id_product;
                    $acs['xsell_cardnumber']    = $row1['field_25'];
                    $acs['xsell_cardtype']      = $row1['field_26'];
                    $acs['xsell_cardowner']     = $row1['field_69'];

                    $acs['xsell_offer1']        = $row1['field_61'];
                    $acs['xsell_offer2']        = $row1['field_62'];

                    $acs['xsell_cardsup1']      = $row1['field_57'];
                    $acs['xsell_cardsup2']      = $row1['field_58'];
                    $acs['xsell_cardsup3']      = $row1['field_59'];
                    $acs['xsell_cardsup4']      = $row1['field_60'];

                    $acs['xsell_cardsupname1']  = $row1['field_53'];
                    $acs['xsell_cardsupname2']  = $row1['field_54'];
                    $acs['xsell_cardsupname3']  = $row1['field_55'];
                    $acs['xsell_cardsupname4']  = $row1['field_56'];

                    if ($row1['field_22'] != '') {
                        $tmp_imp = explode(';', $row1['field_22']);
                        $acs['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $acs['uploadcode']          = $uploadcode;

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $acs);
                    // var_dump($str);
                    array_push($insertData_xsell, $str_xsell);
                    $inserted1++;
                } else {
                    ## Main Data ACS
                    $insert['fullname']            = $row1['field_2'];
                    $insert['social_number']       = $row1['field_3'];
                    $insert['pob']                 = $row1['field_4'];
                    $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_5']);
                    $insert['email']               = $row1['field_19'];
                    $insert['card_number_basic']   = $row1['field_25'];
                    $insert['card_type']           = $row1['field_26'];
                    $insert['creditlimit']         = $row1['field_27'];
                    $insert['card_exp']            = $row1['field_31'];
                    $insert['cif_no']              = $row1['field_40'];
                    $insert['gender']              = $row1['field_52'];
                    $insert['cnum']                = $row1['field_65'];
                    $insert['segment1']            = $row1['field_68'];
                    $insert['dummy_id']            = $row1['field_70'];
                    $insert['segment2']            = $row1['field_71'];
                    $insert['remark']              = $row1['field_73'];

                    ## Try to Fix DOB
                    if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                        $insert['dob']             =  $this->convertTextDatefop($row1['field_5'], '/');
                    }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row1['field_15'];
                    $insert['office_phone1_ori']   = $row1['field_16'];
                    $insert['hp1_ori']             = $row1['field_17'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Default Data
                    $insert['tgl_upload']          = DATE('Y-m-d');
                    $insert['id_campaign']         = $row1['target_campaign'];
                    $insert['uploadcode']          = $uploadcode;

                    ## Data for Verification
                    $insert['bill_statement']      = $row1['field_20'];
                    $insert['autodebet']           = $row1['field_21'];

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);
                    // var_dump($str);

                    array_push($insertData_skip, $str);
                    $inserted_skip++;
                }
            } ## End foreach

        } else { ### For ACT I8 ###
            foreach ($excelDataNoDup as $row1) {
                $insert = array();
                $skip = 0;
                $skip_reason = '';
                $hp1_ori            = $row1['field_18'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                if ($skip == 0) {
                    ## Main Data ACT I8
                    $insert['fullname']            = $row1['field_3'];
                    $insert['social_number']       = $row1['field_8'];
                    $insert['pob']                 = $row1['field_9'];
                    $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_10']);
                    $insert['card_number_basic']   = $row1['field_19'];
                    $insert['card_type']           = $row1['field_20'];
                    $insert['creditlimit']         = $row1['field_25'];
                    $insert['card_exp']            = $row1['field_27'];
                    $insert['cnum']                = $row1['field_28'];
                    $insert['cif_no']              = $row1['field_37'];
                    $insert['cycle']               = $row1['field_29'];
                    $insert['status']              = $row1['field_32'];
                    $insert['segment1']            = $row1['field_38'];
                    $insert['dummy_id']            = $row1['field_41'];
                    $insert['segment2']            = $row1['field_42'];
                    $insert['remarks']             = $row1['field_43'];

                    ## Try to Fix DOB
                    if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                        $insert['dob']             =  $this->convertTextDatefop($row1['field_10'], '/');
                    }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row1['field_16'];
                    $insert['office_phone1_ori']   = $row1['field_17'];
                    $insert['hp1_ori']             = $row1['field_18'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Default Data
                    $insert['tgl_upload']          = DATE('Y-m-d');
                    $insert['id_campaign']         = $row1['target_campaign'];
                    $insert['uploadcode']          = $uploadcode;

                    ## Data for Verification
                    $insert['bill_statement']      = $row1['field_11'];
                    $insert['autodebet']           = $row1['field_12'];

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);

                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $act_i8['cif_no']              = $row1['field_37'];
                    $act_i8['id_campaign']         = $row1['target_campaign'];
                    $act_i8['id_product']          = $id_product;
                    $act_i8['xsell_cardnumber']    = $row1['field_19'];
                    $act_i8['xsell_cardtype']      = $row1['field_20'];
                    $act_i8['xsell_cardowner']     = $row1['field_39'];

                    $act_i8['xsell_offer1']        = $row1['field_30'];
                    $act_i8['xsell_offer2']        = $row1['field_31'];

                    $act_i8['xsell_cardsup1']      = $row1['field_21'];
                    $act_i8['xsell_cardsup2']      = $row1['field_22'];
                    $act_i8['xsell_cardsup3']      = $row1['field_23'];
                    $act_i8['xsell_cardsup4']      = $row1['field_24'];

                    $act_i8['xsell_cardsupname1']  = $row1['field_4'];
                    $act_i8['xsell_cardsupname2']  = $row1['field_5'];
                    $act_i8['xsell_cardsupname3']  = $row1['field_6'];
                    $act_i8['xsell_cardsupname4']  = $row1['field_7'];

                    if ($row1['field_36'] != '') {
                        $tmp_imp = explode(';', $row1['field_36']);
                        $act_i8['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $act_i8['uploadcode']          = $uploadcode;

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $act_i8);

                    array_push($insertData_xsell, $str_xsell);
                    $inserted1++;
                } else {
                    ## Main Data ACT I8
                    $insert['fullname']            = $row1['field_3'];
                    $insert['social_number']       = $row1['field_8'];
                    $insert['pob']                 = $row1['field_9'];
                    $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_10']);
                    $insert['card_number_basic']   = $row1['field_19'];
                    $insert['card_type']           = $row1['field_20'];
                    $insert['creditlimit']         = $row1['field_25'];
                    $insert['card_exp']            = $row1['field_27'];
                    $insert['cnum']                = $row1['field_28'];
                    $insert['cif_no']              = $row1['field_37'];
                    $insert['cycle']               = $row1['field_29'];
                    $insert['status']              = $row1['field_32'];
                    $insert['segment1']            = $row1['field_38'];
                    $insert['dummy_id']            = $row1['field_41'];
                    $insert['segment2']            = $row1['field_42'];
                    $insert['remark']              = $row1['field_43'];

                    ## Try to Fix DOB
                    if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                        $insert['dob']             =  $this->convertTextDatefop($row1['field_10'], '/');
                    }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row1['field_16'];
                    $insert['office_phone1_ori']   = $row1['field_17'];
                    $insert['hp1_ori']             = $row1['field_18'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Default Data
                    $insert['tgl_upload']          = DATE('Y-m-d');
                    $insert['id_campaign']         = $row1['target_campaign'];
                    $insert['uploadcode']          = $uploadcode;

                    ## Data for Verification
                    $insert['bill_statement']      = $row1['field_11'];
                    $insert['autodebet']           = $row1['field_12'];

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);

                    array_push($insertData_skip, $str);
                    $inserted_skip++;
                }
            } ## End foreach
        }

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_xsell, 500);
        $this->multiple_insert($insertData_skip, 500);
        $return['inserted'] = $inserted;
        $return['dup'] = $inserted_skip;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function remove_samebasiccard_acs($excelData, $field)
    {
        $idx = 0;
        $cardBag = array();
        foreach ($excelData as $excelRow) {
            $card = $excelRow[$field];
            if (!empty($card)) {
                $is_samecard = in_array($card, $cardBag, true);
                if ($is_samecard) {
                    unset($excelData[$idx]); //remove from array;
                } else {
                    array_push($cardBag, $card);
                }
            }
            $idx++;
        }
        return array_values($excelData); ## return with reindexed array; 
    }

    ######### Mapping Refferal #######################################
    function mapping_ref($partner)
    {
        $this->load->model('misc/misc_model', 'misc_model');
        $inserted = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori          = $row['field_2'];
            $hp1              = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }

            ## Main Data
            $insert['fullname']         = $row['field_1'];

            ## Default Data
            $insert['cif_no']           = $row['field_3'];
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;
            $insert['skip_reason']      = $skip_reason;
            ## Phone Number
            $insert['hp1_ori']          = $row['field_2'];
            $insert['hp1']              = $this->phone_sensor->recognize($insert['hp1_ori']);

            if ($skip == 0) {
                ## ID TSM
                $insert['id_tsm'] = $this->tsm_incharge;
                ## ID SPV
                $insert['id_spv'] = $this->misc_model->get_tableDataById('tb_users', $row['field_4'], 'username', 'id_leader');
                ## ID TSR
                $insert['id_tsr'] = $this->misc_model->get_tableDataById('tb_users', $row['field_4'], 'username', 'id_user');
                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);
                array_push($insertData, $str);
                $inserted++;
            } else {
                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);
                array_push($insertData_skip, $str);
                $inserted1++;
            }
        } ## End foreach        

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }
    ################## End Mapping Refferal #########################################################      

    ################ Mapping IB ###################################################################
    function mapping_ib($partner)
    {
        $inserted = 0;
        $dup = 0;
        $skipped = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori          = $row['field_17'];
            $hp1              = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }

            ## Main Data
            $insert['branch_name']      = $row['field_4'];
            $insert['cif_no']           = $row['field_8'];
            $insert['fullname']         = $row['field_9'];
            $insert['dob']              = $this->convertTextDatefop($row['field_10']);
            $insert['pob']              = $row['field_11'];
            $insert['social_number']    = $row['field_12'];
            $insert['npwp']             = $row['field_13'];
            $insert['gender']           = $row['field_15'];
            $insert['maiden_name']      = $row['field_16'];

            $insert['home_address1']    = $row['field_20'];
            $insert['home_address2']    = $row['field_21'];
            $insert['company_name']     = $row['field_22'];
            $insert['office_address1']  = $row['field_23'];
            $insert['job_title']        = $row['field_25'];
            $insert['job_position']     = $row['field_26'];
            $insert['datainfo']         = $row['field_33'];
            $insert['dummy_id']         = $row['field_34'];

            ## Phone Number
            $insert['home_phone1_ori']     = $row['field_18'];
            $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
            $insert['office_phone1_ori']   = $row['field_19'];
            $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
            $insert['hp1_ori']             = $row['field_17'];
            $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            ## Data for Verification
            $insert['bill_statement']      = $row['field_28'];
            $insert['autodebet']           = $row['field_29'];

            ## Data Segment
            $insert['segment1']           = $row['field_27'];
            $insert['segment2']           = $row['field_31'];
            $insert['segment3']           = $row['field_36'];

            if ($skip == 0) {
                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);
                array_push($insertData, $str);
                $inserted++;
            } else {
                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);
                array_push($insertData_skip, $str);
                $skipped++;
            }
        } ## End foreach        

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        $return['skip'] = $skipped;
        return $return;
    }
    ################ End Mapping IB ##############################################################

    ############### CPIL DAY 1 #############################################################################
    function autoupload_byengineproduct_cpilx($excelData, $period, $engine, $namcampaign, $partner)
    {
        $enginenumber = 9;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_ntb');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_ntb', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_ntb
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT target_campaign as campaign FROM tb_uploadpreview_ntb limit 1
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproductcpilx($campaigns['campaign'], $period,  $partner);
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_ntb
                LEFT JOIN tb_campaign ON tb_uploadpreview_ntb.target_campaign = tb_campaign.name
                SET tb_uploadpreview_ntb.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);

        ## Start mapping to real table
        // $inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'cpil':
                $res = $this->mapping_cpilx($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function make_campaignproductcpilx($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        $campaign_product = '59';
        $campaign_type = '1';

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 6, 1, intval($period_arr['year']))),
                'remark' => $campaignname . '-' . date('M Y'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    ############### NTB #############################################################################
    function autoupload_byengineproduct_ntb($excelData, $period, $engine, $namcampaign, $partner)
    {
        $enginenumber = 9;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_ntb');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_ntb', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_ntb
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT target_campaign as campaign FROM tb_uploadpreview_ntb limit 1
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproductntb($campaigns['campaign'], $period,  $partner);
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_ntb
                LEFT JOIN tb_campaign ON tb_uploadpreview_ntb.target_campaign = tb_campaign.name
                SET tb_uploadpreview_ntb.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);

        ## Start mapping to real table
        // $inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'sso ib':
                $res = $this->mapping_ib($engine['partner']);
                break;
            case 'msl':
                $res = $this->mapping_msl($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function make_campaignproductntb($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        if ($type == 'sso ib') { ## sso ib
            $campaign_product = '39';
            $campaign_type = '1';
        } elseif ($type == 'msl') { ## msl
            $campaign_product = '39';
            $campaign_type = '1';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 6, 1, intval($period_arr['year']))),
                'remark' => $campaignname . '-' . date('M Y'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    ############################# Mapping MSL #####################################################
    function mapping_msl()
    {
        $inserted = 0;
        $dup = 0;
        $skipped = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori          = $row['field_13'];
            $hp1              = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }

            ## Main Data
            $insert['main_branch_name'] = $row['field_3'];
            $insert['cif_no']           = $row['field_4'];
            $insert['fullname']         = $row['field_5'];
            $insert['dob']              = $this->convertTextDatefop($row['field_6']);
            $insert['pob']              = $row['field_7'];
            $insert['social_number']    = $row['field_8'];
            $insert['npwp']             = $row['field_9'];
            $insert['gender']           = $row['field_11'];
            $insert['maiden_name']      = $row['field_12'];

            $insert['home_address1']    = $row['field_16'];
            $insert['home_address2']    = $row['field_17'];
            $insert['company_name']     = $row['field_18'];
            $insert['office_address1']  = $row['field_19'];
            $insert['job_title']        = $row['field_21'];
            $insert['job_position']     = $row['field_22'];
            $insert['datainfo']         = $row['field_39'];
            $insert['dummy_id']         = $row['field_40'];

            ## Phone Number
            $insert['home_phone1_ori']     = $row['field_14'];
            $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
            $insert['office_phone1_ori']   = $row['field_15'];
            $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
            $insert['hp1_ori']             = $row['field_13'];
            $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

            ## Default Data
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            ## Data Segment
            $insert['segment1']  = $row['field_28'];
            $insert['segment2']  = $row['field_33'];
            $insert['segment3']  = $row['field_42'];

            if ($skip == 0) {
                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);
                array_push($insertData, $str);
                $inserted++;
            } else {
                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);
                array_push($insertData_skip, $str);
                $skipped++;
            }
        } ## End foreach        

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        $return['skip'] = $skipped;
        return $return;
    }

    ###################################### Mapping PVU / UPGRADE ################################################
    function autoupload_byengineproduct_pvu1($excelData, $period, $namcampaign, $type)
    {
        $enginenumber = 8;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );

        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_pvu');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_pvu', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_pvu
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_pvu
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproductpvu($campaigns['campaign'], $period, $type);
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_pvu
                LEFT JOIN tb_campaign ON tb_uploadpreview_pvu.target_campaign = tb_campaign.name
                SET tb_uploadpreview_pvu.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);

        ## Start mapping to real table
        $res = $this->mapping_pvu('upgrade');

        $return = $res;
        return $return;
    }

    function make_campaignproductpvu1($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        if ($type == 'upgrade') { ## PVU UPGRADE
            $campaign_product = '51';
            $campaign_type = '1';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 1, intval($period_arr['day']) + 1, intval($period_arr['year']))),
                'remark' => ucwords($type) . ' ' . date('M Y'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    ###################################### Mapping PVU / UPGRADE ################################################
    function autoupload_byengineproduct_pvu($excelData, $period, $namcampaign, $type)
    {
        $enginenumber = 8;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );

        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_pvu');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_pvu', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_pvu
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_pvu
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproductpvu($campaigns['campaign'], $period, $type);
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_pvu
                LEFT JOIN tb_campaign ON tb_uploadpreview_pvu.target_campaign = tb_campaign.name
                SET tb_uploadpreview_pvu.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);

        ## Start mapping to real table
        $res = $this->mapping_pvu('upgrade');

        $return = $res;
        return $return;
    }

    function make_campaignproductpvu($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        if ($type == 'upgrade') { ## PVU UPGRADE
            $campaign_product = '51';
            $campaign_type = '1';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 1, intval($period_arr['day']) + 1, intval($period_arr['year']))),
                'remark' => ucwords($type) . ' ' . date('M Y'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    function mapping_pvu($partner)
    {
        $inserted = 0;
        $inserted_skip = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_xsell = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_pvu');
        $qArr = $qObj->result_array();

        ## Remove double prospect data ( for transaction on masterdata )
        $excelDataNoDup = $this->remove_samebasiccard_acs($qArr, 'field_48'); ## data & lokasi field PVU UPGRADE

        foreach ($excelDataNoDup as $row1) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row1['field_17'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }

            if ($skip == 0) {
                ## Main Data PVU
                $insert['fullname']            = $row1['field_2'];
                $insert['pob']                 = $row1['field_4'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_5']);
                $insert['card_number_basic']   = $row1['field_24'];
                $insert['card_type']           = $row1['field_25'];
                $insert['creditlimit']         = $row1['field_26'];

                $insert['card_exp']            = $row1['field_30'];
                $insert['cnum']                = $row1['field_47'];
                $insert['cif_no']              = $row1['field_48'];

                $insert['dummy_id']            = $row1['field_49'];

                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row1['field_5'], '/');
                }

                ## Phone Number
                $insert['home_phone1_ori']     = $row1['field_15'];
                $insert['office_phone1_ori']   = $row1['field_16'];
                $insert['hp1_ori']             = $row1['field_17'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Default Data
                $insert['tgl_upload']          = DATE('Y-m-d');
                $insert['id_campaign']         = $row1['target_campaign'];
                $insert['uploadcode']          = $uploadcode;

                ## Data for Verification
                $insert['bill_statement']      = $row1['field_28'];
                $insert['autodebet']           = $row1['field_29'];

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);

                array_push($insertData, $str);

                ## XSEL DATA
                $pvu['cif_no']              = $row1['field_48'];
                $pvu['id_campaign']         = $row1['target_campaign'];
                $pvu['id_product']          = 51;

                $pvu['xsell_cardnumber']    = $row1['field_24'];
                $pvu['xsell_cardtype']      = $row1['field_25'];

                $pvu['xsell_cardsup1']      = $row1['field_31'];
                $pvu['xsell_cardsup2']      = $row1['field_32'];
                $pvu['xsell_cardsup3']      = $row1['field_33'];
                $pvu['xsell_cardsup4']      = $row1['field_34'];

                $pvu['xsell_cardsupname1']  = $row1['field_39'];
                $pvu['xsell_cardsupname2']  = $row1['field_40'];
                $pvu['xsell_cardsupname3']  = $row1['field_41'];
                $pvu['xsell_cardsupname4']  = $row1['field_42'];

                if ($row1['field_21'] != '') {
                    $tmp_imp = explode(';', $row1['field_21']);
                    $pvu['xsell_cardxsell'] = json_encode($tmp_imp);
                }

                $pvu['uploadcode']          = $uploadcode;
                $pvu['tgl_upload']          = DATE('Y-m-d');

                $str = "";
                $str = $this->db->insert_string('tb_xsell', $pvu);

                array_push($insertData_xsell, $str);
                $inserted++;
            } else { ## Insert For Skip
                ## Main Data PVU UPGRADE
                $insert['fullname']            = $row1['field_2'];
                $insert['pob']                 = $row1['field_4'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_5']);
                $insert['card_number_basic']   = $row1['field_24'];
                $insert['card_type']           = $row1['field_25'];
                $insert['creditlimit']         = $row1['field_26'];

                $insert['card_exp']            = $row1['field_30'];
                $insert['cnum']                = $row1['field_47'];
                $insert['cif_no']              = $row1['field_48'];

                $insert['dummy_id']            = $row1['field_49'];

                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row1['field_5'], '/');
                }

                ## Phone Number
                $insert['home_phone1_ori']     = $row1['field_15'];
                $insert['office_phone1_ori']   = $row1['field_16'];
                $insert['hp1_ori']             = $row1['field_17'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Default Data
                $insert['tgl_upload']          = DATE('Y-m-d');
                $insert['id_campaign']         = $row1['target_campaign'];
                $insert['uploadcode']          = $uploadcode;

                ## Data for Verification
                $insert['bill_statement']      = $row1['field_28'];
                $insert['autodebet']           = $row1['field_29'];

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);

                array_push($insertData_skip, $str);
                $inserted_skip++;
            }
        } ## End foreach

        ## MAIN DATA XSELL

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_xsell, 500);
        $this->multiple_insert($insertData_skip, 500);
        $return['inserted'] = $inserted;
        $return['dup'] = $inserted_skip;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    ###################################### Mapping Supplement ################################################
    function autoupload_byengineproduct_sup($excelData, $period, $engine, $namcampaign, $type)
    {
        $enginenumber = 9;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );

        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_sup');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_sup', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_sup
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_sup
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproductsup($campaigns['campaign'], $period, $type);
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_sup
                LEFT JOIN tb_campaign ON tb_uploadpreview_sup.target_campaign = tb_campaign.name
                SET tb_uploadpreview_sup.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);

        ## Start mapping to real table
        // $inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'sup':
                $res = $this->mapping_sup($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function make_campaignproductsup($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        $campaign_product = '47';
        $campaign_type = '1';

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 2, intval($period_arr['day']) + 1, intval($period_arr['year']))),
                'remark' => ucwords($type) . ' ' . date('M Y'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    function mapping_sup($partner)
    {
        $inserted = 0;
        $inserted1 = 0;
        $inserted_skip = 0;
        $dup = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_sup');
        $qArr = $qObj->result_array();

        ## Remove double prospect data ( for transaction on masterdata )
        $excelDataNoDup = $this->remove_samebasiccard_acs($qArr, 'field_32'); ## Filter Cif number

        foreach ($excelDataNoDup as $row1) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row1['field_17'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }

            if ($skip == 0) {
                ## Main Data Supplement
                $insert['fullname']            = $row1['field_2'];
                $insert['pob']                 = $row1['field_4'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_5']);

                $insert['card_number_basic']   = $row1['field_25'];
                $insert['card_type']           = $row1['field_26'];
                $insert['creditlimit']         = $row1['field_27'];

                $insert['card_exp']            = $row1['field_30'];
                $insert['cnum']                = $row1['field_31'];
                $insert['cif_no']              = $row1['field_32'];

                $insert['npwp']                = $row1['field_35'];
                $insert['datainfo']            = $row1['field_42'];

                $insert['dummy_id']            = $row1['field_43'];
                $insert['segment1']            = $row1['field_44'];

                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row1['field_5'], '/');
                }

                ## Phone Number
                $insert['home_phone1_ori']     = $row1['field_15'];
                $insert['office_phone1_ori']   = $row1['field_16'];
                $insert['hp1_ori']             = $row1['field_17'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Default Data
                $insert['tgl_upload']          = DATE('Y-m-d');
                $insert['id_campaign']         = $row1['target_campaign'];
                $insert['uploadcode']          = $uploadcode;

                ## Data for Verification
                $insert['bill_statement']      = $row1['field_20'];
                $insert['autodebet']           = $row1['field_21'];

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);

                array_push($insertData, $str);
                $inserted++;

                ## XSEL DATA
                $sup['cif_no']              = $row1['field_32'];
                $sup['id_campaign']         = $row1['target_campaign'];
                $sup['id_product']          = 47;

                $sup['xsell_cardnumber']    = $row1['field_25'];
                $sup['xsell_cardsup1']      = $row1['field_36'];
                $sup['xsell_cardsup2']      = $row1['field_38'];
                $sup['xsell_cardsup3']      = $row1['field_40'];

                if ($row1['field_22'] != '') {
                    $tmp_imp = explode(';', $row1['field_22']);
                    $sup['xsell_cardxsell'] = json_encode($tmp_imp);
                }

                $sup['uploadcode']          = $uploadcode;

                $str_xsell = "";
                $str_xsell = $this->db->insert_string('tb_xsell', $sup);

                array_push($insertData_dup, $str_xsell);
                $inserted1++;
            } else { ## Insert For Skip
                ## Main Data Supplement
                $insert['fullname']            = $row1['field_2'];
                $insert['pob']                 = $row1['field_4'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row1['field_5']);

                $insert['card_number_basic']   = $row1['field_25'];
                $insert['card_type']           = $row1['field_26'];
                $insert['creditlimit']         = $row1['field_27'];

                $insert['card_exp']            = $row1['field_30'];
                $insert['cnum']                = $row1['field_31'];
                $insert['cif_no']              = $row1['field_32'];

                $insert['npwp']                = $row1['field_35'];
                $insert['datainfo']            = $row1['field_42'];

                $insert['dummy_id']            = $row1['field_43'];
                $insert['segment1']            = $row1['field_44'];

                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row1['field_5'], '/');
                }

                ## Phone Number
                $insert['home_phone1_ori']     = $row1['field_15'];
                $insert['office_phone1_ori']   = $row1['field_16'];
                $insert['hp1_ori']             = $row1['field_17'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Default Data
                $insert['tgl_upload']          = DATE('Y-m-d');
                $insert['id_campaign']         = $row1['target_campaign'];
                $insert['uploadcode']          = $uploadcode;

                ## Data for Verification
                $insert['bill_statement']      = $row1['field_20'];
                $insert['autodebet']           = $row1['field_21'];

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);

                array_push($insertData_skip, $str);
                $inserted_skip++;
            }
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_dup, 500);
        $this->multiple_insert($insertData_skip, 500);
        $return['inserted'] = $inserted;
        $return['dup'] = $inserted_skip;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    ## New Mapping Xsell 
    function autoupload_xsell($excelData, $period, $main_product, $namcampaign)
    {
        $enginenumber = 10;
        $namcampaign = preg_replace('/\s+/', ' ', $namcampaign);
        $idx = 0;
        $loop = 0;
        $insertxsell = 0;
        $data = array();
        $res = array();
        $insertDataXsell = array();

        // echo "<pre>";
        // print_r($excelData); // buang header;
        // echo "</pre>";
        // exit();

        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;        

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = substr($period, 2, 2);
        $period_year = substr($period, 4, 2);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_xsell');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $str = "";
                $str = $this->db->insert_string('tb_uploadpreview_xsell', $previewrow);
                array_push($insertDataXsell, $str);
                $insertxsell++;
            }
        }
        $this->multiple_insert($insertDataXsell, 500);

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_xsell
            SET target_campaign = UPPER('{$namcampaign}')
        ";
        $this->db->query($sql);

        ## SELECT all Target Campaign
        $sql = "SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_xsell";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            if ($main_product == 'COP') {
                $data_dbtype = 0; //$data[1]['field_10'];
                $this->db->where('db_type', $data_dbtype);
                $id_dbtype = $this->db->get('tb_dbtype')->row_array()['idx'];
                $this->make_campaignproduct_xsell($namcampaign, $period, '46', $id_dbtype); ## COP
            } elseif ($main_product == 'FOP') {
                $data_dbtype = 0; //$data[1]['field_10'];
                $this->db->where('db_type', $data_dbtype);
                $id_dbtype = $this->db->get('tb_dbtype')->row_array()['idx'];
                $this->make_campaignproduct_xsell($namcampaign, $period, '44', $id_dbtype); ## FOP
            } elseif ($main_product == 'PL') {
                $this->make_campaignproduct_xsell($namcampaign, $period, '32'); ## PL
            } elseif ($main_product == 'ACS') {
                $this->make_campaignproduct_xsell($namcampaign, $period, '48'); ## ACS
            } elseif ($main_product == 'CPILXCOP') {
                $this->make_campaignproduct_xsell($namcampaign, $period, '57'); ## CPIL
            }
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_xsell
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview_xsell.target_campaign = tb_campaign.name
                SET tb_uploadpreview_xsell.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->query($sql);

        if ($main_product) {
            $res = $this->mapping_xsell($main_product);
        } else {
            $this->upd_enginestatus($enginenumber, '0');
            die('Engine Mapper is Not Registered');
        }

        $return = $res;
        return $return;
    }

    ## New Mapping CPLI x COP 
    function autoupload_cpilxcop($excelData, $period, $main_product, $namcampaign)
    {
        // debug mra
        // var_dump($excelData);die();
        // var_dump($period);die();
        // var_dump($main_product);die();
        // var_dump($namcampaign);die();
        $enginenumber = 10;
        $namcampaign = preg_replace('/\s+/', ' ', $namcampaign);
        $idx = 0;
        $loop = 0;
        $insertxsell = 0;
        $data = array();
        $res = array();
        $insertDataXsell = array();

        // echo "<pre>";
        // print_r($excelData); // buang header;
        // echo "</pre>";
        // exit();

        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;        

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = substr($period, 2, 2);
        $period_year = substr($period, 4, 2);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_xsell');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $str = "";
                $str = $this->db->insert_string('tb_uploadpreview_xsell', $previewrow);
                array_push($insertDataXsell, $str);
                $insertxsell++;
            }
        }
        $this->multiple_insert($insertDataXsell, 500);

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_xsell
            SET target_campaign = UPPER('{$namcampaign}')
        ";
        $this->db->query($sql);

        ## SELECT all Target Campaign
        $sql = "SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_xsell";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        // debug mra
        // var_dump($qArr);die();
        foreach ($qArr as $campaigns) {
            if ($main_product == 'COP') {
                $data_dbtype = 0; //$data[1]['field_10'];
                $this->db->where('db_type', $data_dbtype);
                $id_dbtype = $this->db->get('tb_dbtype')->row_array()['idx'];
                $this->make_campaignproduct_xsell($namcampaign, $period, '46', $id_dbtype); ## COP
            } elseif ($main_product == 'CPILXCOP') {
                // $data_dbtype = 0; //$data[1]['field_10'];
                // $this->db->where('db_type', $data_dbtype);
                // $id_dbtype = $this->db->get('tb_dbtype')->row_array()['idx'];
                // $this->make_campaignproduct_xsell($namcampaign, $period, '57', $id_dbtype); ## CPIL
                $this->make_campaignproduct_xsell($namcampaign, $period, '57'); ## CPIL
                // $this->make_campaignproductcpil($campaigns['campaign'], $period, $type);
            } 
            // elseif ($main_product == 'PL') {
            //     $this->make_campaignproduct_xsell($namcampaign, $period, '32'); ## PL
            // } elseif ($main_product == 'ACS') {
            //     $this->make_campaignproduct_xsell($namcampaign, $period, '48'); ## ACS
            // }
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_xsell
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview_xsell.target_campaign = tb_campaign.name
                SET tb_uploadpreview_xsell.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->query($sql);

        if ($main_product) {

            // debug mra
            // var_dump($main_product);die();
            $res = $this->mapping_cpilxcop($main_product);
        } else {
            $this->upd_enginestatus($enginenumber, '0');
            die('Engine Mapper is Not Registered');
        }

        $return = $res;
        return $return;
    }

    function make_campaignproduct_xsell($campaignname, $period, $main_product = '', $dbtype = '0')
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');
        $qObj->row_array();

        $qaminimum = '100';
        $bcprefix = 'A';

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => $dbtype,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 6, 1, intval($period_arr['year']))),
                'remark' => 'AutoUpload ' . date('Y-m-d H:i:s'),
                'published' => 1,
                'createdate' => date('Y-m-d h:i:s'),
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $main_product,
                'campaign_type' => 1,
                'multiproduct' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );
            $this->db->insert('tb_campaign', $campaignData);
            // debug roe:
            // var_dump($campaignData); die();
        }
    }

    // onmra:
    function mapping_xsell($main_product = '')
    {
        $inserted = 0;
        $inserted1 = 0;
        $inserted_supp = 0;
        $dup = 0;
        $skip = 0;
        $skip_reason = '';
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_trx = array();
        $insertData_sup = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_xsell');
        $qArr = $qObj->result_array();

        if ($main_product == 'COP') {
            $field_dummyid = '49';
        } elseif ($main_product == 'FOP') {
            $field_dummyid = '51';
        } elseif ($main_product == 'PL') {
            $field_dummyid = '82';
        } elseif ($main_product == 'ACS') {
            $field_dummyid = '13';
        } elseif ($main_product == 'CPILXCOP') {
            $field_dummyid = '3';
        }

        ## Remove double prospect data ( for transaction on masterdata )
        $excelDataNoDup = $this->remove_samebasiccard_xsell($qArr, $field_dummyid);

        ### MAIN DATA WITH DATA XSELL & SKIP
        $g = 0;
        foreach ($excelDataNoDup as $row) {
            $skip = 0;
            $skip_reason = '';

            if ($main_product == 'COP') : ## xsell fop & sup
                $hp1_ori            = $row['field_8'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                // Update Enddate Campaign
                if ($g < 1) {
                    $expiredcamp = $qArr[0]['field_41'];
                    $camp_target = $qArr[0]['target_campaign'];
                    $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
                    $satu = $this->update_campaign($expiredcamp2, $camp_target);
                    $this->db->query($satu);
                }

                // End Update Campaign Enddate

                $insert = array();
                if ($skip == 0) {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_41']);

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_41'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    $insert['gender']              = $row['field_5'];
                    $insert['code_tele']           = $row['field_12'];
                    $insert['status']              = $row['field_18']; ## for COP
                    $insert['status2']             = $row['field_20']; ## for FOP
                    $insert['card_number_basic']   = $row['field_15'];
                    $insert['card_type']           = $row['field_13'];
                    $insert['creditlimit']         = $row['field_22'];
                    $insert['available_credit']    = $row['field_22'];

                    ## segment
                    $insert['segment1']            = $row['field_47'];
                    $insert['segment2']            = $row['field_42'];
                    $insert['segment3']            = $row['field_48'];

                    ## Data COP
                    $insert['max_loan']            = $row['field_23'];
                    $insert['loan1']               = $row['field_24'];
                    $insert['loan2']               = $row['field_25'];
                    $insert['loan3']               = $row['field_26'];
                    $insert['cycle']               = $row['field_14'];
                    $insert['custom1']             = $row['field_61'] == 'Y' ? 'R-0': '';

                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];

                    ## Tunggu Info
                    // $insert['rdf']                 = $row['field_47'];

                    $insert['datainfo']            = $row['field_50'];
                    $insert['dummy_id']            = $row['field_49'];
                    $insert['group_loan']          = $row['field_29'];

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);
                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $xsell['cif_no']              = $row['field_3'];
                    $xsell['id_campaign']         = $row['target_campaign'];
                    $xsell['id_product']          = 46; ## COP

                    $xsell['xsell_cardnumber']    = $row['field_15'];
                    $xsell['xsell_cardtype']      = $row['field_13'];
                    $xsell['xsell_cardowner']     = substr($row['field_2'], 0, 10);
                    $xsell['xsell_cardsup1']      = ''; //$row1['field_36'];
                    $xsell['xsell_cardsup2']      = ''; //$row1['field_38'];
                    $xsell['xsell_cardsup3']      = ''; //$row1['field_40'];

                    if ($row['field_44'] != '') {
                        $tmp_imp = explode(';', $row['field_44']);
                        $xsell['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $xsell['uploadcode']          = $uploadcode;
                    $xsell['tgl_upload']          = DATE('Y-m-d');

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $xsell);

                    array_push($insertData_sup, $str_xsell);
                    $inserted_supp++;
                } else {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_41']);

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_41'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    $insert['gender']              = $row['field_5'];
                    $insert['code_tele']           = $row['field_12'];
                    $insert['status']              = $row['field_18']; ## for COP
                    $insert['status2']             = $row['field_20']; ## for FOP
                    $insert['card_number_basic']   = $row['field_15'];
                    $insert['card_type']           = $row['field_13'];
                    $insert['creditlimit']         = $row['field_22'];
                    $insert['available_credit']    = $row['field_22'];

                    ## segment
                    $insert['segment1']            = $row['field_47'];
                    $insert['segment2']            = $row['field_14'];
                    $insert['segment3']            = $row['field_48'];

                    ## Data COP
                    $insert['max_loan']            = $row['field_23'];
                    $insert['loan1']               = $row['field_24'];
                    $insert['loan2']               = $row['field_25'];
                    $insert['loan3']               = $row['field_26'];
                    $insert['cycle']               = $row['field_14'];
                    $insert['custom1']             = $row['field_61'] == 'Y' ? 'R-0': '';

                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];

                    ## Tunggu Info
                    // $insert['rdf']                 = $row['field_47'];

                    $insert['datainfo']            = $row['field_50'];
                    $insert['dummy_id']            = $row['field_49'];
                    $insert['group_loan']          = $row['field_29'];

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);
                    array_push($insertData_skip, $str);
                    $inserted1++;
                }
            elseif ($main_product == 'FOP') : ## main product fop
                $hp1_ori            = $row['field_8'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                // Update Enddate Campaign
                if ($g < 1) {
                    $expiredcamp = $qArr[0]['field_43'];
                    $camp_target = $qArr[0]['target_campaign'];
                    $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
                    $satu = $this->update_campaign($expiredcamp2, $camp_target);
                    $this->db->query($satu);
                }

                // End Update Campaign Enddate

                $insert = array();
                if ($skip == 0) {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_40']);

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_40'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    $insert['gender']              = $row['field_5'];
                    $insert['code_tele']           = $row['field_12'];
                    $insert['status']              = $row['field_15']; ## for COP
                    //$insert['status2']             = $row['field_17']; ## for FOP
                    $insert['status2']             = $row['field_62']; ## for FOP
                    $insert['card_number_basic']   = $row['field_20'];
                    $insert['card_type']           = $row['field_13'];
                    $insert['creditlimit']         = $row['field_19'];
                    $insert['available_credit']    = $row['field_19'];

                    ## Segment
                    $insert['segment1']            = $row['field_49'];
                    // $insert['segment2']            = $row['field_21']; ##segment lama
                    $insert['segment2']            = $row['field_53']; ##segment baru
                    $insert['segment3']            = $row['field_50'];

                    ## Data FOP
                    $insert['max_loan']            = $row['field_30'];
                    $insert['loan1']               = $row['field_31'];
                    $insert['loan2']               = $row['field_32'];
                    $insert['loan3']               = $row['field_33'];
                    $insert['cycle']               = $row['field_14'];

                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];

                    ## Tunggu Info
                    // $insert['rdf']                 = $row['field_47'];

                    $insert['datainfo']            = $row['field_52'];
                    $insert['dummy_id']            = $row['field_51'];
                    $insert['group_loan']          = $row['field_36'];

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];

                    $insert['expired_data']           = $row['field_60'];
                    //$insert['expired_data']           = $row['field_60'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);
                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $xsell['cif_no']              = $row['field_3'];
                    $xsell['id_campaign']         = $row['target_campaign'];
                    $xsell['id_product']          = 44; ## FOP

                    $xsell['xsell_cardnumber']    = $row['field_20'];
                    $xsell['xsell_cardtype']      = $row['field_13'];
                    $xsell['xsell_cardowner']     = $row['field_2'];
                    $xsell['xsell_cardsup1']      = ''; //$row1['field_36'];
                    $xsell['xsell_cardsup2']      = ''; //$row1['field_38'];
                    $xsell['xsell_cardsup3']      = ''; //$row1['field_40'];

                    if ($row['field_46'] != '') {
                        $tmp_imp = explode(';', $row['field_46']);
                        $xsell['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $xsell['uploadcode']          = $uploadcode;
                    $xsell['tgl_upload']          = DATE('Y-m-d');

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $xsell);

                    array_push($insertData_sup, $str_xsell);
                    $inserted_supp++;
                } else {
                    ## Dup Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_40']);

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_40'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    $insert['gender']              = $row['field_5'];
                    $insert['code_tele']           = $row['field_12'];
                    $insert['status']              = $row['field_15']; ## for COP
                    //$insert['status2']             = $row['field_17']; ## for FOP
                    $insert['status2']             = $row['field_62']; ## for FOP
                    $insert['card_number_basic']   = $row['field_20'];
                    $insert['card_type']           = $row['field_13'];
                    $insert['creditlimit']         = $row['field_19'];
                    $insert['available_credit']    = $row['field_19'];

                    ## Segment
                    $insert['segment1']            = $row['field_49'];
                    $insert['segment2']            = $row['field_14'];
                    $insert['segment3']            = $row['field_50'];

                    ## Data COP
                    $insert['max_loan']            = $row['field_30'];
                    $insert['loan1']               = $row['field_31'];
                    $insert['loan2']               = $row['field_32'];
                    $insert['loan3']               = $row['field_33'];
                    $insert['cycle']               = $row['field_14'];

                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];

                    ## Tunggu Info
                    // $insert['rdf']                 = $row['field_47'];

                    $insert['datainfo']            = $row['field_52'];
                    $insert['dummy_id']            = $row['field_51'];
                    $insert['group_loan']          = $row['field_36'];

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);
                    array_push($insertData_skip, $str);
                    $inserted1++;
                }
            elseif ($main_product == 'PL') : ## xsell fop & sup
                $hp1_ori            = $row['field_28'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                $insert = array();
                if ($skip == 0) {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_40']);

                    // ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_40'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_26'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_27'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_28'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    // $insert['gender']              = $row['field_12'];
                    $insert['code_tele']           = $row['field_69'];
                    $insert['status']              = $row['field_54']; ## for Main product PL
                    $insert['status2']             = $row['field_55']; ## for FOP
                    $insert['card_number_basic']   = $row['field_4'];
                    $insert['card_type']           = $row['field_6'];
                    $insert['creditlimit']         = $row['field_8'];
                    $insert['available_credit']    = $row['field_8'];

                    ## Segment
                    $insert['segment1']            = $row['field_54'];
                    $insert['segment2']            = $row['field_74'];
                    $insert['segment3']            = $row['field_81'];

                    ## Plafon
                    $insert['plafon12']            = $row['field_43'];
                    $insert['plafon24']            = $row['field_45'];
                    $insert['plafon36']            = $row['field_47'];

                    $insert['cycle']               = $row['field_29'];
                    $insert['cif_no']              = $row['field_42'];
                    $insert['cnum']                = $row['field_3'];
                    // $insert['home_address1']       = $row['field_15'];
                    // $insert['home_address2']       = $row['field_16'];
                    // $insert['home_city']           = $row['field_17'];
                    // $insert['home_zipcode']        = $row['field_18'];

                    $insert['datainfo']            = $row['field_80'];
                    $insert['dummy_id']            = $row['field_82'];
                    // $insert['group_loan']          = $row['field_35']; 

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_52'];
                    $insert['autodebet']           = $row['field_53'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);
                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $xsell['cif_no']              = $row['field_42'];
                    $xsell['id_campaign']         = $row['target_campaign'];
                    $xsell['id_product']          = 32; ## PL

                    $xsell['xsell_cardnumber']    = $row['field_4'];
                    $xsell['xsell_cardtype']      = $row['field_6'];
                    $xsell['xsell_cardowner']     = $row['field_2'];
                    $xsell['xsell_cardsup1']      = ''; //$row1['field_36'];
                    $xsell['xsell_cardsup2']      = ''; //$row1['field_38'];
                    $xsell['xsell_cardsup3']      = ''; //$row1['field_40'];

                    if ($row['field_75'] != '') {
                        $tmp_imp = explode(';', $row['field_75']);
                        $xsell['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $xsell['uploadcode']          = $uploadcode;
                    $xsell['tgl_upload']          = DATE('Y-m-d');

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $xsell);

                    array_push($insertData_sup, $str_xsell);
                    $inserted_supp++;
                } else {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_40']);

                    // ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_40'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_26'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_27'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_28'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    // $insert['gender']              = $row['field_12'];
                    $insert['code_tele']           = $row['field_69'];
                    $insert['status']              = $row['field_54']; ## for Main product PL
                    $insert['status2']             = $row['field_55']; ## for FOP
                    $insert['card_number_basic']   = $row['field_4'];
                    $insert['card_type']           = $row['field_6'];
                    $insert['creditlimit']         = $row['field_8'];
                    $insert['available_credit']    = $row['field_8'];

                    ## Segment
                    $insert['segment1']            = $row['field_54'];
                    $insert['segment2']            = $row['field_74'];
                    $insert['segment3']            = $row['field_81'];

                    ## Plafon
                    $insert['plafon12']            = $row['field_43'];
                    $insert['plafon24']            = $row['field_45'];
                    $insert['plafon36']            = $row['field_47'];

                    $insert['cycle']               = $row['field_29'];
                    $insert['cif_no']              = $row['field_42'];
                    $insert['cnum']                = $row['field_3'];
                    // $insert['home_address1']       = $row['field_15'];
                    // $insert['home_address2']       = $row['field_16'];
                    // $insert['home_city']           = $row['field_17'];
                    // $insert['home_zipcode']        = $row['field_18'];

                    $insert['datainfo']            = $row['field_80'];
                    $insert['dummy_id']            = $row['field_82'];
                    // $insert['group_loan']          = $row['field_35']; 

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_52'];
                    $insert['autodebet']           = $row['field_53'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);
                    array_push($insertData_skip, $str);
                    $inserted1++;
                }

            elseif ($main_product == 'ACS') : ## xsell ACS 
                $hp1_ori            = $row['field_9'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                // Update Enddate Campaign
                if ($g < 1) {
                    $expiredcamp = $qArr[0]['field_64'];
                    $camp_target = $qArr[0]['target_campaign'];
                    $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
                    $satu = $this->update_campaign($expiredcamp2, $camp_target);
                    $this->db->query($satu);
                }
                // End Update Campaign Enddate

                if ($skip == 0) {
                    ## Main Data ACS & ACT
                    $insert['fullname']            = $row['field_2'];
                    $insert['social_number']       = $row['field_5'];
                    // $insert['pob']                 = $row['field_4'];  
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_5']);
                    // $insert['email']               = $row['field_19'];
                    $insert['datainfo']            = $row['field_45'];
                    $insert['card_number_basic']   = $row['field_13'];
                    $insert['card_type']           = $row['field_14'];
                    // $insert['card_exp']            = $row['field_31'];
                    $insert['cif_no']              = $row['field_3'];
                    $insert['gender']              = $row['field_6'];
                    $insert['cnum']                = $row['field_4'];
                    $insert['dummy_id']            = $row['field_44'];
                    $insert['segment1']            = $row['field_42'];
                    $insert['segment2']            = $row['field_22'];
                    $insert['segment3']            = $row['field_43'];
                    $insert['cycle']               = $row['field_27'];

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_5'], '/');
                    // }

                    ## Data COP
                    $insert['status']              = $row['field_26'];
                    $insert['creditlimit']         = $row['field_30'];
                    $insert['max_loan']            = $row['field_31'];
                    $insert['loan1']               = $row['field_32'];
                    $insert['loan2']               = $row['field_33'];
                    $insert['loan3']               = $row['field_34'];

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_7'];
                    $insert['office_phone1_ori']   = $row['field_8'];
                    $insert['hp1_ori']             = $row['field_9'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Default Data
                    $insert['tgl_upload']          = DATE('Y-m-d');
                    $insert['id_campaign']         = $row['target_campaign'];
                    $insert['uploadcode']          = $uploadcode;

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_10'];
                    $insert['autodebet']           = $row['field_11'];

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);

                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $acs['cif_no']              = $row['field_3'];
                    $acs['id_campaign']         = $row['target_campaign'];
                    $acs['id_product']          = '48';
                    $acs['xsell_cardnumber']    = $row['field_13'];
                    $acs['xsell_cardtype']      = $row['field_14'];
                    $acs['xsell_cardowner']     = $row['field_2'];

                    $acs['xsell_offer1']        = $row['field_16'];
                    $acs['xsell_offer2']        = $row['field_17'];

                    // $acs['xsell_cardsup1']      = $row['field_57'];
                    // $acs['xsell_cardsup2']      = $row['field_58'];
                    // $acs['xsell_cardsup3']      = $row['field_59'];
                    // $acs['xsell_cardsup4']      = $row['field_60'];

                    // $acs['xsell_cardsupname1']  = $row['field_53'];
                    // $acs['xsell_cardsupname2']  = $row['field_54'];
                    // $acs['xsell_cardsupname3']  = $row['field_55'];
                    // $acs['xsell_cardsupname4']  = $row['field_56'];

                    if ($row['field_40'] != '') {
                        $tmp_imp = explode(';', $row['field_40']);
                        $acs['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $acs['uploadcode']          = $uploadcode;

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $acs);
                    // var_dump($str);
                    array_push($insertData_sup, $str_xsell);
                } else {
                    ## Main Data ACS & ACT
                    $insert['fullname']            = $row['field_2'];
                    $insert['social_number']       = $row['field_5'];
                    // $insert['pob']                 = $row['field_4'];  
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_5']);
                    // $insert['email']               = $row['field_19'];
                    $insert['datainfo']            = $row['field_45'];
                    $insert['card_number_basic']   = $row['field_13'];
                    $insert['card_type']           = $row['field_14'];
                    // $insert['card_exp']            = $row['field_31'];
                    $insert['cif_no']              = $row['field_3'];
                    $insert['gender']              = $row['field_6'];
                    $insert['cnum']                = $row['field_4'];
                    $insert['dummy_id']            = $row['field_44'];
                    $insert['segment1']            = $row['field_42'];
                    $insert['segment2']            = $row['field_22'];
                    $insert['segment3']            = $row['field_43'];
                    $insert['cycle']               = $row['field_27'];

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_5'], '/');
                    // }

                    ## Data COP
                    $insert['status']              = $row['field_26'];
                    $insert['creditlimit']         = $row['field_30'];
                    $insert['max_loan']            = $row['field_31'];
                    $insert['loan1']               = $row['field_32'];
                    $insert['loan2']               = $row['field_33'];
                    $insert['loan3']               = $row['field_34'];

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_7'];
                    $insert['office_phone1_ori']   = $row['field_8'];
                    $insert['hp1_ori']             = $row['field_9'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Default Data
                    $insert['tgl_upload']          = DATE('Y-m-d');
                    $insert['id_campaign']         = $row['target_campaign'];
                    $insert['uploadcode']          = $uploadcode;

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_10'];
                    $insert['autodebet']           = $row['field_11'];

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);

                    array_push($insertData_skip, $str);
                    $inserted1++;
                }
            elseif ($main_product == 'CPILXCOP') : ## xsell CPIL // onmra:
                $skip = 0;
                $skip_reason = '';
                $hp1_ori            = $row['field_8'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                // Update Enddate Campaign
                if ($g < 1) {
                    $expiredcamp = $qArr[0]['field_26'];
                    $camp_target = $qArr[0]['target_campaign'];
                    $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
                    $satu = $this->update_campaign($expiredcamp2, $camp_target);
                    $this->db->query($satu);
                }
                // End Update Campaign Enddate

                $insert = array();
                if ($skip == 0) {
                    $insert['fullname']            = $row['field_2'];
                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];
                    $insert['gender']              = $row['field_5'];

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];
                    $insert['card_number']   = $row['field_14'];
                    
                    $insert['card_exp']               = $row['field_13'];
                    $insert['max_loan']            = $row['field_38'] ? $row['field_38'] : null;
                    $insert['loan1']               = $row['field_39'] ? $row['field_39'] : null;
                    $insert['loan2']               = $row['field_40'] ? $row['field_40'] : null;

                    $insert['annual_rate']          = $row['field_31']; //onmra:
                    // $insert['jenis_kartu']            = $row['field_12']; ###
                    $insert['jenis_kartu']            = ''; ### jenis kartu cpil selalu null
                    $insert['expired_data']          = $row['field_26'];
                    // $insert['tenor']                = $row['field_23'] ? $row['field_23'] : null; // ga di pake

                    $insert['status']               = $row['field_36']; ## for COP
                    $insert['status2']              = $row['field_54']; ## Status CPIL 
                    $insert['custom1']              = $row['field_53'] == 'Y' ? 'R-0': '';
                    $insert['datainfo_xsell']       = $row['field_58'];

                    $insert['group_loan']           = $row['field_17'];
                    $insert['creditlimit']          = $row['field_15'];
                    $insert['available_credit']     = $row['field_16'] ? $row['field_16'] : 0;

                    ## segment
                    $insert['segment1']            = $row['field_65'];
                    $insert['segment1']            = $row['field_27'];
                    // cpil 
                    // $insert['expired_data']        = $row['field_34']; perlu konfirmasi
                    $insert['dummy_id']             = $row['field_60'];
                    $insert['card_type']          = $row['field_55']; // card type COP
                    $insert['cycle']             = $row['field_56'];// cycle cop
                    $insert['card_number_basic']          = $row['field_57']; // card number cop
                    $insert['datainfo']       = $row['field_64']; // data info cop
                    $insert['status_rate_cpil']     = $row['field_33']; // status AKA rate cpil


                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);
                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $xsell['cif_no']              = $row['field_3'];
                    $xsell['id_campaign']         = $row['target_campaign'];
                    $xsell['id_product']          = '57'; ## CPIL

                    $xsell['xsell_cardnumber']    = $row['field_57'];
                    $xsell['xsell_cardtype']      = $row['field_55'];
                    $xsell['xsell_cardowner']     = substr($row['field_2'], 0, 10); 
                    $xsell['xsell_cardsup1']      = ''; //$row1['field_36'];
                    $xsell['xsell_cardsup2']      = ''; //$row1['field_38'];
                    $xsell['xsell_cardsup3']      = ''; //$row1['field_40'];

                    if ($row['field_47'] != '') {
                        $tmp_imp = explode(';', $row['field_47']);
                        $xsell['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $xsell['uploadcode']          = $uploadcode;
                    $xsell['tgl_upload']          = DATE('Y-m-d');

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $xsell);

                    array_push($insertData_sup, $str_xsell);
                    $inserted_supp++;
                } else {
                    
                    $insert['fullname']            = $row['field_2'];
                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];
                    $insert['gender']              = $row['field_5'];

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];
                    $insert['card_number']   = $row['field_14'];
                    
                    $insert['card_exp']               = $row['field_13'];
                    $insert['max_loan']            = $row['field_38'] ? $row['field_38'] : null;
                    $insert['loan1']               = $row['field_39'] ? $row['field_39'] : null;
                    $insert['loan2']               = $row['field_40'] ? $row['field_40'] : null;

                    $insert['annual_rate']          = $row['field_31']; //onmra:
                    // $insert['jenis_kartu']            = $row['field_12']; ###
                    $insert['jenis_kartu']            = ''; ### jenis kartu cpil selalu null
                    $insert['expired_data']          = $row['field_26'];
                    // $insert['tenor']                = $row['field_23'] ? $row['field_23'] : null; // ga di pake

                    $insert['status']               = $row['field_36']; ## for COP
                    $insert['status2']              = $row['field_54']; ## Status CPIL 
                    $insert['custom1']              = $row['field_53'] == 'Y' ? 'R-0': '';
                    $insert['datainfo_xsell']       = $row['field_58'];

                    $insert['group_loan']           = $row['field_17'];
                    $insert['creditlimit']          = $row['field_15'];
                    $insert['available_credit']     = $row['field_16'] ? $row['field_16'] : 0;

                    ## segment
                    $insert['segment1']            = $row['field_65'];
                    $insert['segment1']            = $row['field_27'];
                    // cpil 
                    // $insert['expired_data']        = $row['field_34']; perlu konfirmasi
                    $insert['dummy_id']             = $row['field_60'];
                    $insert['card_type']          = $row['field_55']; // card type COP
                    $insert['cycle']             = $row['field_56'];// cycle cop
                    $insert['card_number_basic']          = $row['field_57']; // card number cop
                    $insert['datainfo']       = $row['field_64']; // data info cop
                    $insert['status_rate_cpil']     = $row['field_33']; // status AKA rate cpil


                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);

                    array_push($insertData_skip, $str);
                    $inserted1++;
                }
            endif;
            $g++;
        } ## End foreach

        ### TRANSAKSI FOP
        $trxInsert = 0; ## declare jumlah insert trx 
        if ($main_product == 'COP') : ## COP
            foreach ($qArr as $row) {
                $fop = array();
                if ($row['field_51']) ## Jika tipe kartu true 
                {
                    ##FOP Data
                    $fop['cif_no']              = $row['field_3'];
                    $fop['cnum']                = $row['field_4'];
                    $fop['id_campaign']         = $row['target_campaign'];
                    $fop['card_basic']          = $row['field_15'];
                    $fop['trx_card']            = $row['field_17'];
                    $fop['trx_cardtype']        = $row['field_51'];
                    $fop['trx_reff']            = $row['field_32'];
                    $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_35']);
                    $fop['trx_amount']          = $row['field_33'];
                    $fop['trx_description']     = $row['field_34'];
                    $fop['trx_countcard']       = $row['field_46'];
                    $fop['uploadcode']          = $uploadcode;

                    $str = "";
                    $str = $this->db->insert_string('tb_trxdetail', $fop);
                    array_push($insertData_trx, $str);
                    $trxInsert++;
                }
            } ## End foreach
        elseif ($main_product == 'FOP') : ## FOP
            foreach ($qArr as $row) {
                $fop = array();
                if ($row['field_53']) ## Jika tipe kartu true 
                {
                    ##FOP Data
                    $fop['cif_no']              = $row['field_3'];
                    $fop['cnum']                = $row['field_4'];
                    $fop['id_campaign']         = $row['target_campaign'];
                    $fop['card_basic']          = $row['field_20'];
                    $fop['trx_card']            = $row['field_22'];
                    // $fop['trx_cardtype']        = $row['field_53']; ##maping lama
                    $fop['trx_cardtype']        = $row['field_13']; ##maping baru
                    $fop['trx_reff']            = $row['field_23'];
                    $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_27']);
                    $fop['trx_amount']          = $row['field_24'];
                    $fop['trx_description']     = $row['field_26'];
                    $fop['trx_countcard']       = $row['field_48'];
                    $fop['uploadcode']          = $uploadcode;

                    $str = "";
                    $str = $this->db->insert_string('tb_trxdetail', $fop);
                    array_push($insertData_trx, $str);
                    $trxInsert++;
                }
            } ## End foreach
        elseif ($main_product == 'PL') : ## PL
            foreach ($qArr as $row) {
                $fop = array();
                if ($row['field_83']) ## Jika tipe kartu true 
                {
                    ##FOP Data
                    $fop['cif_no']              = $row['field_42'];
                    $fop['cnum']                = $row['field_3'];
                    $fop['id_campaign']         = $row['target_campaign'];
                    $fop['card_basic']          = $row['field_4'];
                    $fop['trx_card']            = $row['field_5'];
                    $fop['trx_cardtype']        = $row['field_83'];
                    $fop['trx_reff']            = $row['field_9'];
                    $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_62']);
                    $fop['trx_amount']          = $row['field_58'];
                    $fop['trx_description']     = $row['field_61'];
                    $fop['trx_countcard']       = $row['field_79'];
                    $fop['uploadcode']          = $uploadcode;

                    $str = "";
                    $str = $this->db->insert_string('tb_trxdetail', $fop);
                    array_push($insertData_trx, $str);
                    $trxInsert++;
                }
            } ## End foreach
        elseif ($main_product == 'CPILXCOP') : ## CPIL
            foreach ($qArr as $row) {
                $fop = array();
                if ($row['field_12']) ## Jika tipe kartu true 
                {
                    ##FOP Data
                    $fop['cif_no']              = $row['field_3'];
                    $fop['cnum']                = $row['field_4'];
                    $fop['id_campaign']         = $row['target_campaign'];
                    $fop['card_basic']          = $row['field_14'];
                    $fop['trx_card']            = ''; // $row['field_17'];
                    $fop['trx_cardtype']        = $row['field_12'];
                    $fop['trx_reff']            = ''; // $row['field_32'];
                    $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_21']);
                    $fop['trx_amount']          = $row['field_37'] ? $row['field_37'] : null;
                    $fop['trx_description']     = ''; //$row['field_34'];
                    $fop['trx_countcard']       = '0'; //$row['field_46'];
                    $fop['uploadcode']          = $uploadcode;

                    $str = "";
                    $str = $this->db->insert_string('tb_trxdetail', $fop);
                    array_push($insertData_trx, $str);
                    $trxInsert++;
                }
            } ## End foreach
        endif;

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        ## Detail trx FOP
        $this->multiple_insert($insertData_trx, 500);

        ## Detail trx SUP
        $this->multiple_insert($insertData_sup, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $inserted1;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function remove_samebasiccard_xsell($excelData, $field)
    {
        $idx = 0;
        $cardBag = array();
        foreach ($excelData as $excelRow) {
            // $card = $excelRow['field_14'];
            $card = $excelRow['field_' . $field];
            if (!empty($card)) {
                $is_samecard = in_array($card, $cardBag, true);
                if ($is_samecard) {
                    unset($excelData[$idx]); //remove from array;
                } else {
                    array_push($cardBag, $card);
                }
            }
            $idx++;
        }
        return array_values($excelData); ## return with reindexed array; 
    }


    ###################################### Autoupload & Mapping Casa ################################################
    function remove_samebasiccard_casa($excelData, $field)
    {
        $idx = 0;
        $cardBag = array();
        foreach ($excelData as $excelRow) {
            $card = $excelRow[$field];
            if (!empty($card)) {
                $is_samecard = in_array($card, $cardBag, true);
                if ($is_samecard) {
                    unset($excelData[$idx]); //remove from array;
                } else {
                    array_push($cardBag, $card);
                }
            }
            $idx++;
        }
        return array_values($excelData); ## return with reindexed array; 
    }

    function make_campaignproductcasa($campaignname, $period, $type)
    {

        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');
        // var_dump($qObj);
        // die();
        if ($type == 'casa') { ## casa
            $campaign_product = '49';
            $campaign_type = '1';
            $dbtype = '6';
        } elseif ($type == 'uplan') { ## UPLAN
            $campaign_product = '55';
            $campaign_type = '1';
            $dbtype = '8';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }


        if ($qObj->num_rows() == 0) { ## create new campaign
            // $potong_name_campaign = substr($campaignname, 5);
            // var_dump($potong_name_campaign);
            // die();
            ## make campaign
            $campaignData = array(
                // 'name' =>  $potong_name_campaign,
                // 'origin' =>  $potong_name_campaign,
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => $dbtype,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']) + 31, intval($period_arr['year']))),
                'remark' => 'AutoUpload ' . date('Y-m-d H:i:s'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => $campaign_type,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );
            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    function autoupload_byengineproduct_casa($excelData, $period, $engine, $namcampaign, $type)
    {
        $potong_name_campaign = substr($namcampaign, 5);
        // var_dump($namcampaign);
        // die();
        $enginenumber = 10;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }
        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_casa');

        foreach ($data as $previewrow) {

            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_casa', $previewrow);
            }
        }

        ## update target campaign
        $sql = "UPDATE tb_uploadpreview_casa
            SET target_campaign = CONCAT(UPPER('{$potong_name_campaign}'), ' ')";
        $this->db->simple_query($sql);
        // die($sql);
        ## SELECT all Target Campaign
        $sql = "SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_casa";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();
        // die($sql);
        foreach ($qArr as $campaigns) {
            $this->make_campaignproductcasa($campaigns['campaign'], $period,  $type);
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_casa
                LEFT JOIN tb_campaign 
                    ON tb_uploadpreview_casa.target_campaign = tb_campaign.name
                SET tb_uploadpreview_casa.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);
        // die($sql);
        ## Start mapping to real table
        //$inserted = $this->mapping_cc();


        switch ($engine['engine']) {
            case 'casa':
                $res = $this->mapping_casa($engine['partner'], '49');
                break;
            case 'uplan':
                $res = $this->mapping_casa($engine['partner'], '55');
                break;
                // var_dump($engine['engine']);
                // die();
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function mapping_casa($partner)
    {
        $inserted = 0;
        $inserted1 = 0;
        $dup = 0;
        $skip = 0;
        $skip_reason = '';
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_casa');
        $qArr = $qObj->result_array();
        $qObj->free_result();

        ## Remove DUPLICATE prospect data 
        $excelDataNoDup = $this->remove_samebasiccard_casa($qArr, 'field_41'); ## Filter Cif number

        foreach ($excelDataNoDup as $row) {
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row['field_13'];
            //$hp1                = $row['field_12'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1_ori));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1_ori;
                } //this data will skiped
                $qObj->free_result();
            }
            //echo $this->db->last_query();  
            //var_dump($skip_reason);
            //die(); field
            $insert = array();
            if ($skip == 0) {
                ## Main Data
                $insert['custom2']             = $row['field_1'];
                $insert['card_type']           = $row['field_2'];
                $insert['custom3']             = $row['field_3'];
                $insert['cif_no']              = $row['field_4'];
                $insert['cnum']                = $row['field_5'];

                $insert['no_reff']             = $row['field_7'];
                $insert['status']              = $row['field_8'];
                $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_9']);
                if ($insert['dob'] == '') {
                    $insert['dob'] = NULL;
                }
                // $insert['dummy_id']            = $row['field_3'];
                ## Try to Fix DOB
                if (substr($insert['dob'], 0, 4) >= date('Y') || $insert['dob'] == '0000-00-00') {
                    $insert['dob']             =  $this->convertTextDatefop($row['field_9'], '/');
                }
                $insert['job_title']           = $row['field_10'];
                $insert['fullname']            = $row['field_11'];
                $insert['gender']              = $row['field_14'];
                $insert['email']               = $row['field_15'];
                $insert['branch_name']         = $row['field_16'];
                $insert['code_tele']           = $row['field_17'];
                $insert['office_zipcode']      = $row['field_18'];
                $insert['last_calltime']       = $row['field_19'];
                $insert['segment1']            = $row['field_20'];
                $insert['segment2']            = $row['field_21'];
                $insert['segment3']            = $row['field_22'];
                $insert['datainfo']            = $row['field_23'];

                $insert['autodebet']           = $row['field_24'];
                $insert['bill_statement']      = $row['field_25'];
                $insert['card_number_basic']   = $row['field_26'];

                ## Phone Number

                $insert['hp2_ori']                = $row['field_12'];
                $insert['hp2']                    = $this->phone_sensor->recognize($insert['hp2_ori']);
                $insert['hp1_ori']                = $row['field_13'];
                $insert['hp1']                    = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Default Data
                $insert['id_tsm']           = '1277';
                $insert['tgl_upload']         = DATE('Y-m-d');
                $insert['id_campaign']        = $row['target_campaign'];
                $insert['uploadcode']         = $uploadcode;
                $insert['skip_reason']        = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);
                // die($str);

                array_push($insertData, $str);
                $inserted++;
            } else {
                ## Main Data
                $insert_skip['custom2']             = $row['field_1'];
                $insert_skip['card_type']           = $row['field_2'];
                $insert_skip['custom3']             = $row['field_3'];
                $insert_skip['cif_no']              = $row['field_4'];
                $insert_skip['cnum']                = $row['field_5'];

                $insert_skip['no_reff']             = $row['field_7'];
                $insert_skip['status']              = $row['field_8'];
                $insert_skip['dob']                 = $this->convertExcelToNormalDateTRP($row['field_9']);
                // $insert_skip['dummy_id']            = $row['field_3'];
                ## Try to Fix DOB
                if (substr($insert_skip['dob'], 0, 4) >= date('Y') || $insert_skip['dob'] == '0000-00-00') {
                    $insert_skip['dob']             =  $this->convertTextDatefop($row['field_9'], '/');
                }
                $insert_skip['job_title']           = $row['field_10'];
                $insert_skip['fullname']            = $row['field_11'];
                $insert_skip['gender']              = $row['field_14'];
                $insert_skip['email']               = $row['field_15'];
                $insert_skip['branch_name']         = $row['field_16'];
                $insert_skip['code_tele']           = $row['field_17'];
                $insert_skip['office_zipcode']      = $row['field_18'];
                $insert_skip['last_calltime']       = $row['field_19'];
                $insert_skip['segment1']            = $row['field_20'];
                $insert_skip['segment2']            = $row['field_21'];
                $insert_skip['segment3']            = $row['field_22'];
                $insert_skip['datainfo']            = $row['field_23'];

                $insert_skip['autodebet']           = $row['field_24'];
                $insert_skip['bill_statement']      = $row['field_25'];
                $insert_skip['card_number_basic']   = $row['field_26'];
                ## Phone Number

                $insert_skip['hp2_ori']                = $row['field_12'];
                $insert_skip['hp2']                    = $this->phone_sensor->recognize($insert['hp2_ori']);
                $insert_skip['hp1_ori']                = $row['field_13'];
                $insert_skip['hp1']                    = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Default Data
                $insert_skip['id_tsm']           = '1277';
                $insert_skip['tgl_upload']       = DATE('Y-m-d');
                $insert_skip['id_campaign']      = $row['target_campaign'];
                $insert_skip['uploadcode']       = $uploadcode;
                $insert_skip['skip_reason']       = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert_skip);

                array_push($insertData_skip, $str);
                $inserted1++;
            }
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $inserted1;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    function update_campaign($expiredcamp2, $camp_target)
    {
        $updatecampaign = "UPDATE tb_campaign SET enddate='$expiredcamp2' WHERE id_campaign=$camp_target";
        return $updatecampaign;
    }

    function getCsvCabang($fullpath)
    {
        $return = "";
        $checkfile = is_file($fullpath);
        if ($checkfile) {
            $csvdata = file_get_contents($fullpath);
            $return = $csvdata;
            unset($csvdata);
        } else {
            echo '-No Readable File or Locked';
            die();
        }
        return $return;
    }

    function update_priority_casa($csvdata_row, $delimiter)
    {
        //var_dump($csvdata_row);die();
        unset($csvdata_row[0]); // Remove Header File;
        $updated = 0;
        foreach ($csvdata_row as $row) {
            $row = preg_replace('/\"/', "", $row);
            // var_dump($row);
            // die();
            if ($row != '') {
                $fields = explode($delimiter, $row);
                $id_prospect = $fields[1] * 1;
                // var_dump($fields);
                // die();
                if ($id_prospect > 0) {
                    ## Update Status
                    $update = array(
                        'is_priority' => $fields[2],
                        // 'pickup_reason' => $fields[27],
                        // 'pickupresult_date' => $fields[30],
                        // 'ms_code' => $fields[31],
                        // 'ms_name' => $fields[32]
                    );
                    $this->db->where('id_prospect', $fields[1]);
                    $this->db->update('tb_prospect', $update);
                    $aff = $this->db->affected_rows();
                    //var_dump($this->db->last_query());die();

                    ## Insert Status Track
                    // if ($aff > 0 && $fields[25] != '') {
                    //     $updated++;
                    //     $insert = array(
                    //         'id_prospect' => $fields[1],
                    //         'print_idx' => $fields[0],
                    //         'courier_code' => $fields[31],
                    //         'courier_name' => $fields[32],
                    //         'date_pickup' => $fields[23],
                    //         'status_code' => $fields[25],
                    //         'status_reason' => $fields[27],
                    //         'update_by' => '1',
                    //         'update_type' => 'PKU'
                    //     );
                    //     $this->db->insert('tb_statustrack', $insert);
                    //var_dump($this->db->last_query());
                    // }
                }
            }
        }
        return $updated;
    }

    ###################################### Mapping CPIL ################################################
    function autoupload_byengineproduct_cpil($excelData, $period, $engine, $namcampaign, $type)
    {
        $enginenumber = 9;
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );

        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_cpil');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_cpil', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_cpil
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT DISTINCT target_campaign as campaign FROM tb_uploadpreview_cpil
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $this->make_campaignproductcpil($campaigns['campaign'], $period, $type);
        }

        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_cpil
                LEFT JOIN tb_campaign ON tb_uploadpreview_cpil.target_campaign = tb_campaign.name
                SET tb_uploadpreview_cpil.target_campaign = tb_campaign.id_campaign
        ";
        $this->db->simple_query($sql);

        ## Start mapping to real table
        // $inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'cpil':
                $res = $this->mapping_cpil($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function make_campaignproductcpil($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');

        $campaign_product = '57';
        $campaign_type = '1';

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']) + 2, intval($period_arr['day']) + 1, intval($period_arr['year']))),
                // 'remark' => ucwords($type) . ' ' . date('M Y'),
                'remark' => 'AutoUpload ' . date('Y-m-d H:i:s'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 1,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
        }
    }

    // onmra:
    function mapping_cpil($partner)
    {
        $inserted = 0;
        $inserted1 = 0;
        $dup = 0;
        $skip = 0;
        $skip_reason = '';
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_cpil');
        $qArr = $qObj->result_array();
        $qObj->free_result();

        ## Remove DUPLICATE prospect data 
        $excelDataNoDup = $this->remove_samebasiccard_acs($qArr, 'field_13'); ## Filter Cif number

        foreach ($excelDataNoDup as $row) { //onmra:
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row['field_6'];
            //$hp1                = $row['field_12'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1_ori));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1_ori;
                } //this data will skiped
                $qObj->free_result();
            }
            //echo $this->db->last_query();  
            //var_dump($skip_reason);
            //die(); field
            // Update Enddate Campaign
            $expiredcamp = $qArr[0]['field_14'];
            $camp_target = $qArr[0]['target_campaign'];
            $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
            $satu = $this->update_campaign($expiredcamp2, $camp_target);
            $this->db->simple_query($satu);
            // End Update Campaign Enddate

            $insert = array();
            if ($skip == 0) {
                ## Main Data
                $insert['fullname']            = $row['field_2'];

                $insert['cif_no']              = $row['field_13'];
                $insert['cnum']                = $row['field_20'];
                $insert['gender']              = $row['field_3'];

                ## Phone Number
                $insert['home_phone1_ori']     = $row['field_4'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1_ori']   = $row['field_5'];
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1_ori']             = $row['field_6'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Data for Verification
                $insert['bill_statement']      = $row['field_7'];
                $insert['autodebet']           = $row['field_8'];
                $insert['card_number_basic']   = $row['field_9'];

                // $insert['code_tele']           = $row['field_12'];
                // $insert['card_type']           = $row['field_13'];

                $insert['cycle']               = $row['field_12'];

                $insert['status']              = $row['field_35']; ## for CPIL
                $insert['status2']             = $row['field_21']; ## for CPIL

                $insert['creditlimit']         = $row['field_10'];
                $insert['available_credit']    = $row['field_11'];

                ## Data CPIL
                // $insert['max_loan']            = $row['field_10'];
                // $insert['loan1']               = $row['field_11'];

                $insert['group_loan']          = $row['field_19'];

                ## segment
                $insert['segment1']            = $row['field_15'];
                // $insert['segment2']            = $row['field_40'];
                // $insert['segment3']            = $row['field_53'];

                $insert['expired_data']        = $row['field_22'];

                $insert['datainfo']            = $row['field_29'];
                $insert['dummy_id']            = $row['field_31'];
                $insert['custom1']             = $row['field_37'] == 'Y' ? 'R-0': '';

                ## Default Data
                $insert['tgl_upload']       = DATE('Y-m-d');
                $insert['id_campaign']      = $row['target_campaign'];
                $insert['uploadcode']       = $uploadcode;
                $insert['skip_reason']      = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);
                array_push($insertData, $str);
                $inserted++;
            } else {
                ## Main Data
                $insert_skip['fullname']            = $row['field_2'];

                $insert_skip['cif_no']              = $row['field_13'];
                $insert_skip['cnum']                = $row['field_20'];
                $insert_skip['gender']              = $row['field_3'];

                ## Phone Number
                $insert_skip['home_phone1_ori']     = $row['field_4'];
                $insert_skip['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert_skip['office_phone1_ori']   = $row['field_5'];
                $insert_skip['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert_skip['hp1_ori']             = $row['field_6'];
                $insert_skip['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                ## Data for Verification
                $insert_skip['bill_statement']      = $row['field_7'];
                $insert_skip['autodebet']           = $row['field_8'];
                $insert_skip['card_number_basic']   = $row['field_9'];

                //  $insert_skip['code_tele']           = $row['field_12'];
                //  $insert_skip['card_type']           = $row['field_13'];

                $insert_skip['cycle']               = $row['field_12'];

                $insert_skip['status']              = $row['field_35']; ## for CPIL
                $insert_skip['status2']             = $row['field_21']; ## for CPIL

                $insert_skip['creditlimit']         = $row['field_10'];
                $insert_skip['available_credit']    = $row['field_10'];

                ## Data CPIL
                $insert_skip['max_loan']            = $row['field_10'];
                $insert_skip['loan1']               = $row['field_11'];

                $insert_skip['group_loan']          = $row['field_19'];

                ## segment
                $insert_skip['segment1']            = $row['field_15'];
                //  $insert_skip['segment2']            = $row['field_40'];
                //  $insert_skip['segment3']            = $row['field_53'];

                $insert_skip['expired_data']        = $row['field_22'];

                $insert_skip['datainfo']            = $row['field_29'];
                $insert_skip['dummy_id']            = $row['field_31'];
                $insert_skip['custom1']             = $row['field_37'] == 'Y' ? 'R-0': '';

                ## Default Data
                $insert_skip['tgl_upload']       = DATE('Y-m-d');
                $insert_skip['id_campaign']      = $row['target_campaign'];
                $insert_skip['uploadcode']       = $uploadcode;
                $insert_skip['skip_reason']      = $skip_reason;

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert_skip);

                array_push($insertData_skip, $str);
                $inserted1++;
            }
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $inserted1;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    ################ Mapping CPIL DAY 1 ###################################################################
    function mapping_cpilx($partner)
    {
        $inserted = 0;
        $dup = 0;
        $skipped = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_ntb');
        $qArr = $qObj->result_array();

        foreach ($qArr as $row) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori          = $row['field_20'];
            $hp1              = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }

            ## Main Data
            //$insert['branch_name']      = $row['field_1'];
            $insert['cif_no']           = $row['field_1'];
            $insert['cnum']      = $row['field_2'];
            $insert['fullname']         = $row['field_3'];
            $insert['customer_behavior']         = $row['field_4'];
            $insert['npwp']         = $row['field_5'];
            $insert['email']         = $row['field_6'];
            $insert['maiden_name']      = $row['field_7'];
            $insert['available_credit']    = $row['field_9'];
            $insert['social_number']    = $row['field_10'];
            $insert['home_address1']    = $row['field_11'];
            $insert['home_city']    = $row['field_12'];
            $insert['home_zipcode']    = $row['field_13'];
            $insert_skip['gender']              = $row['field_17'];
            $insert_skip['main_branch_name']              = $row['field_18'];
            $insert_skip['embossing_name']              = $row['field_20'];
            $insert_skip['marital_status']              = $row['field_21'];
            $insert['pob']              = $row['field_22'];
            $insert['dob']              = $this->convertTextDatefop($row['field_23']);
            $insert_skip['title']              = $row['field_24'];
            $insert_skip['plafon12']              = $row['field_25'];
            //$insert['npwp']             = $row['field_13'];
            $insert['gender']           = $row['field_17'];

            //$insert['home_address2']    = $row['field_21'];


            ## Phone Number
            $insert['home_phone1_ori']     = $row['field_26'];
            $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
            $insert['office_phone1_ori']   = $row['field_38'];
            $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
            $insert['hp1_ori']             = $row['field_27'];
            $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

            $insert_skip['business_code']              = $row['field_28'];
            $insert['company_name']     = $row['field_29'];
            $insert['job_title']        = $row['field_30'];
            $insert['office_address1']  = $row['field_31'];
            $insert['office_city']      = $row['field_32'];
            $insert['office_zipcode']   = $row['field_33'];
            $insert['bidang_usaha']        = $row['field_34'];
            $insert['job_position']     = $row['field_35'];
            $insert_skip['avail_limit']              = $row['field_39'];
            //$insert['employment_status']        = $row['field_29'];
            //$insert['datainfo']        = $row['field_32'];
            $insert['status']         = $row['field_41'];
            //$insert['datainfo']         = $row['field_33'];
            $insert['dummy_id']         = $row['field_42'];

            ## Default Data
            $insert['tgl_upload']       = DATE('Y-m-d');
            $insert['id_campaign']      = $row['target_campaign'];
            $insert['uploadcode']       = $uploadcode;

            ## Data for Verification
            //$insert['bill_statement']      = $row['field_28'];
            //$insert['autodebet']           = $row['field_29'];

            ## Data Segment
            //$insert['segment1']           = $row['field_27'];
            //$insert['segment2']           = $row['field_31'];
            //$insert['segment3']           = $row['field_36'];

            if ($skip == 0) {
                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);
                array_push($insertData, $str);
                $inserted++;
            } else {
                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);
                array_push($insertData_skip, $str);
                $skipped++;
            }
        } ## End foreach        

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $dup;
        $return['uploadcode'] = $uploadcode;
        $return['skip'] = $skipped;
        return $return;
    }

    ############################# FOP BALCON #############################################################
    function remove_samebasiccard_fopbalcon($excelData)
    {
        $idx = 0;
        $cardBag = array();
        foreach ($excelData as $excelRow) {
            $card = $excelRow['field_44'];
            if (!empty($card)) {
                $is_samecard = in_array($card, $cardBag, true);
                if ($is_samecard) {
                    unset($excelData[$idx]); //remove from array;
                } else {
                    array_push($cardBag, $card);
                }
            }
            $idx++;
        }
        return array_values($excelData); ## return with reindexed array; 
    }

    function make_campaignproductfopbaclon($campaignname, $period, $type)
    {
        $period_arr['day'] = substr($period, 0, 2);
        $period_arr['month'] = substr($period, 2, 2);
        $period_arr['year'] = substr($period, 4, 4);

        $qaminimum = '100';
        $bcprefix = 'A';

        ## Check if campaign available
        $this->db->where('name', $campaignname);
        $qObj = $this->db->get('tb_campaign');
        //echo $qObj->row_array()['id_campaign'];

        if ($type == 'fp') { ## FOP
            $campaign_product = '60';
            $campaign_type = '8';
        } else {
            $campaign_product = '0'; //DEFAULT
            $campaign_type = '0'; //DEFAULT
        }

        $insert_id = '';

        if ($qObj->num_rows() == 0) { ## create new campaign
            ## make campaign
            $campaignData = array(
                'name' => $campaignname,
                'origin' => $campaignname,
                'db_type' => 0,
                'begindate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']), intval($period_arr['year']))),
                'enddate' => DATE('Y-m-d', mktime(0, 0, 0, intval($period_arr['month']), intval($period_arr['day']) + 13, intval($period_arr['year']))),
                'remark' => 'FOP STMT ' . date('M Y'),
                'published' => 1,
                'id_user_created' => 1, ## this is administrator / system
                'campaign_product' => $campaign_product,
                'campaign_type' => 8,
                'bcprefix' => $bcprefix,
                'qaminimum' => $qaminimum
            );

            $this->db->insert('tb_campaign', $campaignData);
            $insert_id = $this->db->insert_id();
        } else {
            $insert_id = $qObj->row_array()['id_campaign'];
        }
        return $insert_id;
    }

    function autoupload_byengineproduct_fopbalcon($excelData, $period, $engine, $namcampaign)
    {
        $enginenumber = 6;
        $namcampaign = preg_replace('/\s+/', ' ', $namcampaign);
        $idx = 0;
        $loop = 0;
        $data = array();
        $res = array();
        foreach ($excelData as $row) {
            $totalfield = count($row);
            $field = 1;
            foreach ($row as $column) {
                $data[$idx]['field_' . $field] = $column;
                $field++;
            }
            $idx++;
        }

        unset($excelData);
        unset($data[0]); // buang header;

        ## Segment Period
        $period_array = array(
            '01' => 'JAN',
            '02' => 'FEB',
            '03' => 'MAR',
            '04' => 'APR',
            '05' => 'MAY',
            '06' => 'JUN',
            '07' => 'JUL',
            '08' => 'AUG',
            '09' => 'SEP',
            '10' => 'OCT',
            '11' => 'NOV',
            '12' => 'DEC'
        );
        $period_day = substr($period, 0, 2);
        $period_month = $period_array[substr($period, 2, 2)];
        $period_year = substr($period, 4, 4);

        ## clear preview
        $this->db->simple_query('TRUNCATE TABLE tb_uploadpreview_fop');

        foreach ($data as $previewrow) {
            if ($previewrow['field_1'] != "") {
                $this->db->insert('tb_uploadpreview_fop', $previewrow);
            }
        }

        ## update target campaign
        $sql = "
            UPDATE tb_uploadpreview_fop
            SET target_campaign = CONCAT(UPPER('{$namcampaign}'), ' ')
        ";
        $this->db->simple_query($sql);
        ## SELECT all Target Campaign
        $sql = "
            SELECT target_campaign as campaign FROM tb_uploadpreview_fop limit 1
        ";
        $qObj = $this->db->query($sql);
        $qArr = $qObj->result_array();

        foreach ($qArr as $campaigns) {
            $id_campaign = $this->make_campaignproductfopbaclon($campaigns['campaign'], $period,  $type = 'fp');
        }
        // var_dump($id_campaign);
        ## Update target id_campaign
        $sql = "UPDATE tb_uploadpreview_fop SET target_campaign = {$id_campaign}";
        // LEFT JOIN tb_campaign ON tb_uploadpreview_fop.target_campaign = tb_campaign.name
        //  SET tb_uploadpreview_fop.target_campaign = tb_campaign.id_campaign
        // ";

        $this->db->simple_query($sql);

        ## Start mapping to real table
        // $inserted = $this->mapping_cc();
        switch ($engine['engine']) {
            case 'fp':
                $res = $this->mapping_fopbalcon($engine['partner']);
                break;
            default:
                $this->upd_enginestatus($enginenumber, '0');
                die('Engine Mapper is Not Registered');
                break;
        }
        $return = $res;
        return $return;
    }

    function mapping_fopbalcon($partner)
    {
        $inserted = 0;
        $dup = 0;
        $totalSkip = 0;
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_trx = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_fop');
        $qArr = $qObj->result_array();

        ## Remove double prospect data ( for transaction on masterdata )
        $excelDataNoDup = $this->remove_samebasiccard_fopbalcon($qArr);

        foreach ($excelDataNoDup as $row1) {
            $insert = array();
            $skip = 0;
            $skip_reason = '';
            $hp1_ori            = $row1['field_8'];
            $hp1                = $this->phone_sensor->recognize($hp1_ori);

            ## Check if blacklisted phone_number
            if ($hp1 != '') {
                $this->db->where('type', 'phonenumber');
                $this->db->where('value', $this->db->escape_str($hp1));
                $this->db->where('is_active', 1);
                $qObj = $this->db->get('tb_blacklist');
                if ($qObj->num_rows() > 0) {
                    $skip = 1;
                    $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                } //this data will skiped
            }

            if ($skip == 0) {
                ## Main Data
                $insert['fullname']            = $row1['field_2'];
                $insert['cif_no']              = $row1['field_3'];
                $insert['cnum']                = $row1['field_4'];
                $insert['gender']              = $row1['field_5'];
                ## Phone Number
                $insert['home_phone1_ori']     = $row1['field_6'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1_ori']   = $row1['field_7'];
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1_ori']             = $row1['field_8'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);
                ## Data for Verification
                $insert['bill_statement']      = $row1['field_9'];
                $insert['autodebet']           = $row1['field_11'];

                $insert['cycle']               = $row1['field_13'];
                $insert['card_type']           = $row1['field_12'];
                $insert['status']              = $row1['field_14'];
                $insert['creditlimit']         = $row1['field_15'];
                $insert['available_credit']    = $row1['field_15'];
                $insert['card_number_basic']   = $row1['field_16'];

                // $insert['total_tagihan']       = $row1['field_24'];
                $insert['max_loan']            = $row1['field_24'];
                $insert['plafon12']          = $row1['field_27'];
                // $insert['last_taken']          = $row1['field_27'];
                $insert['segment1']            = $row1['field_45'];
                $insert['segment2']            = $row1['field_47'];

                $insert['loan2']             = $row1['field_30'];
                // $insert['payment']             = $row1['field_30'];
                $insert['loan1']              = $row1['field_31'];
                // $insert['eligible_fop']        = $row1['field_31'];
                $insert['expired_data']        = $row1['field_25'];
                $insert['interest']            = $row1['field_32'];
                // $insert['due_date']            = $row1['field_32'];

                $insert['datainfo']            = $row1['field_41'];
                $insert['plafon24']           = $row1['field_42'];
                $insert['plafon36']           = $row1['field_43'];
                // $insert['datainfo1']           = $row1['field_42'];
                // $insert['datainfo2']           = $row1['field_43'];
                $insert['dummy_id']            = $row1['field_44'];
                ## Default Data
                $insert['tgl_upload']          = DATE('Y-m-d');
                $insert['id_campaign']         = $row1['target_campaign'];
                $insert['uploadcode']          = $uploadcode;

                $str = "";
                $str = $this->db->insert_string('tb_prospect', $insert);

                array_push($insertData, $str);
                $inserted++;

                //var_dump($insertData);echo "<br>";

            } else {
                ## Main Data
                $insert['fullname']            = $row1['field_2'];
                $insert['cif_no']              = $row1['field_3'];
                $insert['cnum']                = $row1['field_4'];
                $insert['gender']              = $row1['field_5'];
                ## Phone Number
                $insert['home_phone1_ori']     = $row1['field_6'];
                $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                $insert['office_phone1_ori']   = $row1['field_7'];
                $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                $insert['hp1_ori']             = $row1['field_8'];
                $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);
                ## Data for Verification
                $insert['bill_statement']      = $row1['field_9'];
                $insert['autodebet']           = $row1['field_11'];

                $insert['cycle']               = $row1['field_13'];
                $insert['card_type']           = $row1['field_12'];
                $insert['status']              = $row1['field_14'];
                $insert['creditlimit']         = $row1['field_15'];
                $insert['available_credit']    = $row1['field_15'];
                $insert['card_number_basic']   = $row1['field_16'];

                // $insert['total_tagihan']       = $row1['field_24'];
                $insert['max_loan']            = $row1['field_24'];
                $insert['plafon12']          = $row1['field_27'];
                // $insert['last_taken']          = $row1['field_27'];
                $insert['segment1']            = $row1['field_45'];
                $insert['segment2']            = $row1['field_47'];

                $insert['loan2']             = $row1['field_30'];
                // $insert['payment']             = $row1['field_30'];
                $insert['loan1']              = $row1['field_31'];
                // $insert['eligible_fop']        = $row1['field_31'];
                $insert['expired_data']        = $row1['field_25'];
                $insert['interest']            = $row1['field_32'];
                // $insert['due_date']            = $row1['field_32'];

                $insert['datainfo']            = $row1['field_41'];
                $insert['plafon24']           = $row1['field_42'];
                $insert['plafon36']           = $row1['field_43'];
                // $insert['datainfo1']           = $row1['field_42'];
                // $insert['datainfo2']           = $row1['field_43'];
                $insert['dummy_id']            = $row1['field_44'];

                ## Data Group_Loan
                // $insert['group_loan']          = $row1['field_34'];

                $str = "";
                $str = $this->db->insert_string('tb_prospect_skip', $insert);

                array_push($insertData_skip, $str);
                $totalSkip++;
            }
            //  $inserted++;
        } ## End foreach

        $trxInsert = 0; ## declare jumlah insert trx 
        foreach ($qArr as $row) {
            $fop = array();
            ##FOP Data
            $fop['cif_no']              = $row['field_3'];
            $fop['cnum']                = $row['field_4'];
            $fop['id_campaign']         = $row['target_campaign'];
            $fop['card_basic']          = $row['field_16'];
            $fop['trx_card']            = $row['field_18'];
            $fop['trx_cardtype']        = $row['field_12'];
            $fop['trx_reff']            = $row['field_19'];
            $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_22']);
            $fop['total_tagihan']       = $row['field_24'];
            $fop['trx_amount']          = $row['field_20'];
            $fop['trx_description']     = $row['field_21'];
            $fop['trx_countcard']       = $row['field_46'];
            $fop['uploadcode']          = $uploadcode;

            $str = "";
            $str = $this->db->insert_string('tb_trxdetail', $fop);

            array_push($insertData_trx, $str);
            $trxInsert++;
        } ## End foreach

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_trx, 500);
        $this->multiple_insert($insertData_skip, 500);
        $return['inserted'] = $inserted;
        $return['trxInsert'] = $trxInsert;
        $return['dup'] = $totalSkip;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }

    // maping cpil x cop
    function mapping_cpilxcop($main_product = '')
    {
        $inserted = 0;
        $inserted1 = 0;
        $inserted_supp = 0;
        $dup = 0;
        $skip = 0;
        $skip_reason = '';
        $return = array();
        $uploadcode = uniqid();
        $unixtime = time();

        ## Prepare Data
        $insertData = array();
        $insertData_dup = array();
        $insertData_trx = array();
        $insertData_sup = array();
        $insertData_skip = array();

        ## Get preview data;
        $qObj = $this->db->get('tb_uploadpreview_xsell');
        $qArr = $qObj->result_array();

        // command mra: unique excel culumn
        if ($main_product == 'COP') {
            $field_dummyid = '49';
        } elseif ($main_product == 'CPIL') {
            // $field_dummyid = '3';
        }

        ## Remove double prospect data ( for transaction on masterdata )
        $excelDataNoDup = $this->remove_samebasiccard_xsell($qArr, $field_dummyid);

        ### MAIN DATA WITH DATA XSELL & SKIP
        $g = 0;
        foreach ($excelDataNoDup as $row) {
            $skip = 0;
            $skip_reason = '';

            if ($main_product == 'COP') : ## xsell fop & sup
                $hp1_ori            = $row['field_8'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                // Update Enddate Campaign
                if ($g < 1) {
                    $expiredcamp = $qArr[0]['field_41'];
                    $camp_target = $qArr[0]['target_campaign'];
                    $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
                    $satu = $this->update_campaign($expiredcamp2, $camp_target);
                    $this->db->query($satu);
                }

                // End Update Campaign Enddate

                $insert = array();
                if ($skip == 0) {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_41']);

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_41'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    $insert['gender']              = $row['field_5'];
                    $insert['code_tele']           = $row['field_12'];
                    $insert['status']              = $row['field_18']; ## for COP
                    $insert['status2']             = $row['field_20']; ## for FOP
                    $insert['card_number_basic']   = $row['field_15'];
                    $insert['card_type']           = $row['field_13'];
                    $insert['creditlimit']         = $row['field_22'];
                    $insert['available_credit']    = $row['field_22'];

                    ## segment
                    $insert['segment1']            = $row['field_47'];
                    $insert['segment2']            = $row['field_42'];
                    $insert['segment3']            = $row['field_48'];

                    ## Data COP
                    // $insert['max_loan']            = $row['field_23'];
                    // $insert['loan1']               = $row['field_24'];
                    // $insert['loan2']               = $row['field_25'];
                    // $insert['loan3']               = $row['field_26'];
                    $insert['cycle']               = $row['field_14'];
                    $insert['max_loan']            = $row['field_37'];
                    $insert['loan1']               = $row['field_38'];
                    $insert['loan2']               = $row['field_39'];
                    $insert['available_credit']    = $row['field_36'];
                    $insert['creditlimit']         = $row['field_36'];

                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];

                    ## Tunggu Info
                    // $insert['rdf']                 = $row['field_47'];

                    $insert['datainfo']            = $row['field_50'];
                    $insert['dummy_id']            = $row['field_49'];
                    $insert['group_loan']          = $row['field_29'];

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);
                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $xsell['cif_no']              = $row['field_3'];
                    $xsell['id_campaign']         = $row['target_campaign'];
                    $xsell['id_product']          = 46; ## COP

                    $xsell['xsell_cardnumber']    = $row['field_15'];
                    $xsell['xsell_cardtype']      = $row['field_13'];
                    $xsell['xsell_cardowner']     = $row['field_2'];
                    $xsell['xsell_cardsup1']      = ''; //$row1['field_36'];
                    $xsell['xsell_cardsup2']      = ''; //$row1['field_38'];
                    $xsell['xsell_cardsup3']      = ''; //$row1['field_40'];

                    if ($row['field_44'] != '') {
                        $tmp_imp = explode(';', $row['field_44']);
                        $xsell['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $xsell['uploadcode']          = $uploadcode;
                    $xsell['tgl_upload']          = DATE('Y-m-d');

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $xsell);

                    array_push($insertData_sup, $str_xsell);
                    $inserted_supp++;
                } else {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_41']);

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_41'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    $insert['gender']              = $row['field_5'];
                    $insert['code_tele']           = $row['field_12'];
                    $insert['status']              = $row['field_18']; ## for COP
                    $insert['status2']             = $row['field_20']; ## for FOP
                    $insert['card_number_basic']   = $row['field_15'];
                    $insert['card_type']           = $row['field_13'];
                    $insert['creditlimit']         = $row['field_22'];
                    $insert['available_credit']    = $row['field_22'];

                    ## segment
                    $insert['segment1']            = $row['field_47'];
                    $insert['segment2']            = $row['field_14'];
                    $insert['segment3']            = $row['field_48'];

                    ## Data COP
                    $insert['max_loan']            = $row['field_23'];
                    $insert['loan1']               = $row['field_24'];
                    $insert['loan2']               = $row['field_25'];
                    $insert['loan3']               = $row['field_26'];
                    $insert['cycle']               = $row['field_14'];
                    $insert['max_loan']            = $row['field_37'];
                    $insert['loan1']               = $row['field_38'];
                    $insert['loan2']               = $row['field_39'];
                    $insert['available_credit']    = $row['field_36'];
                    $insert['creditlimit']         = $row['field_36'];

                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];

                    ## Tunggu Info
                    // $insert['rdf']                 = $row['field_47'];

                    $insert['datainfo']            = $row['field_50'];
                    $insert['dummy_id']            = $row['field_49'];
                    $insert['group_loan']          = $row['field_29'];

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);
                    array_push($insertData_skip, $str);
                    $inserted1++;
                }
            elseif ($main_product == 'CPIL') : ## main product cpil
                $hp1_ori            = $row['field_8'];
                // debug mra
                // var_dump($row['field_2']);die();
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                // Update Enddate Campaign
                // if ($g < 1) {
                if (false) { // format date excel invalid (45458) 
                    $expiredcamp = $qArr[0]['field_26'];
                    // debig mra:
                    // var_dump($qArr[0]); die();
                    $camp_target = $qArr[0]['target_campaign'];
                    $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
                    // var_dump($expiredcamp2); die();
                    $satu = $this->update_campaign($expiredcamp2, $camp_target);
                    $this->db->query($satu);
                }

                // End Update Campaign Enddate

                $insert = array();
                if ($skip == 0) {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_40']);

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_40'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    $insert['gender']              = $row['field_5'];
                    // $insert['code_tele']           = $row['field_12']; ###
                    $insert['status']              = $row['field_35']; ## for COP
                    //$insert['status2']             = $row['field_17']; 
                    // $insert['status2']             = $row['field_62']; ###
                    $insert['card_number_basic']   = $row['field_14'];
                    $insert['card_type']           = $row['field_12']; ###
                    $insert['creditlimit']         = $row['field_16']; ###
                    // $insert['available_credit']    = $row['field_19']; ###

                    ## Segment
                    $insert['segment1']            = $row['field_27'];
                    // $insert['segment2']            = $row['field_21']; ##segment lama
                    // $insert['segment2']            = $row['field_53']; ###
                    // $insert['segment3']            = $row['field_50']; ###

                    ## Data FOP
                    // $insert['max_loan']            = $row['field_30']; ###
                    // $insert['loan1']               = $row['field_31']; ###
                    // $insert['loan2']               = $row['field_32']; ###
                    // $insert['loan3']               = $row['field_33']; ###
                    // $insert['cycle']               = $row['field_14']; ###

                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];

                    ## Tunggu Info
                    // $insert['rdf']                 = $row['field_47'];

                    // $insert['datainfo']            = $row['field_52'];
                    // $insert['dummy_id']            = $row['field_22'];
                    $insert['group_loan']          = $row['field_17'];

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];

                    // $insert['expired_data']           = $row['field_60']; ###
                    //$insert['expired_data']           = $row['field_60'];
                    // addtional 
                    $insert['cycle']      = $row['field_13'];
                    $insert['visa_credit_limit']      = $row['field_15'];
                    $insert['tenor']      = $row['field_23'];
                    $insert['annual_rate']      = $row['field_31'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);
                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $xsell['cif_no']              = $row['field_3'];
                    $xsell['id_campaign']         = $row['target_campaign'];
                    $xsell['id_product']          = 44; ## FOP

                    $xsell['xsell_cardnumber']    = $row['field_20'];
                    $xsell['xsell_cardtype']      = $row['field_13'];
                    $xsell['xsell_cardowner']     = $row['field_2'];
                    $xsell['xsell_cardsup1']      = ''; //$row1['field_36'];
                    $xsell['xsell_cardsup2']      = ''; //$row1['field_38'];
                    $xsell['xsell_cardsup3']      = ''; //$row1['field_40'];

                    if ($row['field_46'] != '') {
                        $tmp_imp = explode(';', $row['field_46']);
                        $xsell['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $xsell['uploadcode']          = $uploadcode;
                    $xsell['tgl_upload']          = DATE('Y-m-d');

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $xsell);

                    array_push($insertData_sup, $str_xsell);
                    $inserted_supp++;
                } else {
                    ## Dup Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_40']);

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_40'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_6'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_7'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_8'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    $insert['gender']              = $row['field_5'];
                    $insert['code_tele']           = $row['field_12'];
                    $insert['status']              = $row['field_15']; ## for COP
                    //$insert['status2']             = $row['field_17']; ## for FOP
                    $insert['status2']             = $row['field_62']; ## for FOP
                    $insert['card_number_basic']   = $row['field_20'];
                    $insert['card_type']           = $row['field_13'];
                    $insert['creditlimit']         = $row['field_19'];
                    $insert['available_credit']    = $row['field_19'];

                    ## Segment
                    $insert['segment1']            = $row['field_49'];
                    $insert['segment2']            = $row['field_14'];
                    $insert['segment3']            = $row['field_50'];

                    ## Data COP
                    $insert['max_loan']            = $row['field_30'];
                    $insert['loan1']               = $row['field_31'];
                    $insert['loan2']               = $row['field_32'];
                    $insert['loan3']               = $row['field_33'];
                    $insert['cycle']               = $row['field_14'];

                    $insert['cif_no']              = $row['field_3'];
                    $insert['cnum']                = $row['field_4'];

                    ## Tunggu Info
                    // $insert['rdf']                 = $row['field_47'];

                    $insert['datainfo']            = $row['field_52'];
                    $insert['dummy_id']            = $row['field_51'];
                    $insert['group_loan']          = $row['field_36'];

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_9'];
                    $insert['autodebet']           = $row['field_11'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);
                    array_push($insertData_skip, $str);
                    $inserted1++;
                }
            elseif ($main_product == 'PL') : ## xsell fop & sup
                $hp1_ori            = $row['field_28'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                $insert = array();
                if ($skip == 0) {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_40']);

                    // ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_40'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_26'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_27'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_28'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    // $insert['gender']              = $row['field_12'];
                    $insert['code_tele']           = $row['field_69'];
                    $insert['status']              = $row['field_54']; ## for Main product PL
                    $insert['status2']             = $row['field_55']; ## for FOP
                    $insert['card_number_basic']   = $row['field_4'];
                    $insert['card_type']           = $row['field_6'];
                    $insert['creditlimit']         = $row['field_8'];
                    $insert['available_credit']    = $row['field_8'];

                    ## Segment
                    $insert['segment1']            = $row['field_54'];
                    $insert['segment2']            = $row['field_74'];
                    $insert['segment3']            = $row['field_81'];

                    ## Plafon
                    $insert['plafon12']            = $row['field_43'];
                    $insert['plafon24']            = $row['field_45'];
                    $insert['plafon36']            = $row['field_47'];

                    $insert['cycle']               = $row['field_29'];
                    $insert['cif_no']              = $row['field_42'];
                    $insert['cnum']                = $row['field_3'];
                    // $insert['home_address1']       = $row['field_15'];
                    // $insert['home_address2']       = $row['field_16'];
                    // $insert['home_city']           = $row['field_17'];
                    // $insert['home_zipcode']        = $row['field_18'];

                    $insert['datainfo']            = $row['field_80'];
                    $insert['dummy_id']            = $row['field_82'];
                    // $insert['group_loan']          = $row['field_35']; 

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_52'];
                    $insert['autodebet']           = $row['field_53'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);
                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $xsell['cif_no']              = $row['field_42'];
                    $xsell['id_campaign']         = $row['target_campaign'];
                    $xsell['id_product']          = 32; ## PL

                    $xsell['xsell_cardnumber']    = $row['field_4'];
                    $xsell['xsell_cardtype']      = $row['field_6'];
                    $xsell['xsell_cardowner']     = $row['field_2'];
                    $xsell['xsell_cardsup1']      = ''; //$row1['field_36'];
                    $xsell['xsell_cardsup2']      = ''; //$row1['field_38'];
                    $xsell['xsell_cardsup3']      = ''; //$row1['field_40'];

                    if ($row['field_75'] != '') {
                        $tmp_imp = explode(';', $row['field_75']);
                        $xsell['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $xsell['uploadcode']          = $uploadcode;
                    $xsell['tgl_upload']          = DATE('Y-m-d');

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $xsell);

                    array_push($insertData_sup, $str_xsell);
                    $inserted_supp++;
                } else {
                    ## Main Data
                    $insert['fullname']            = $row['field_2'];
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_40']);

                    // ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_40'], '/');
                    // }

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_26'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1_ori']   = $row['field_27'];
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1_ori']             = $row['field_28'];
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    // $insert['gender']              = $row['field_12'];
                    $insert['code_tele']           = $row['field_69'];
                    $insert['status']              = $row['field_54']; ## for Main product PL
                    $insert['status2']             = $row['field_55']; ## for FOP
                    $insert['card_number_basic']   = $row['field_4'];
                    $insert['card_type']           = $row['field_6'];
                    $insert['creditlimit']         = $row['field_8'];
                    $insert['available_credit']    = $row['field_8'];

                    ## Segment
                    $insert['segment1']            = $row['field_54'];
                    $insert['segment2']            = $row['field_74'];
                    $insert['segment3']            = $row['field_81'];

                    ## Plafon
                    $insert['plafon12']            = $row['field_43'];
                    $insert['plafon24']            = $row['field_45'];
                    $insert['plafon36']            = $row['field_47'];

                    $insert['cycle']               = $row['field_29'];
                    $insert['cif_no']              = $row['field_42'];
                    $insert['cnum']                = $row['field_3'];
                    // $insert['home_address1']       = $row['field_15'];
                    // $insert['home_address2']       = $row['field_16'];
                    // $insert['home_city']           = $row['field_17'];
                    // $insert['home_zipcode']        = $row['field_18'];

                    $insert['datainfo']            = $row['field_80'];
                    $insert['dummy_id']            = $row['field_82'];
                    // $insert['group_loan']          = $row['field_35']; 

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_52'];
                    $insert['autodebet']           = $row['field_53'];

                    ## Default Data
                    $insert['tgl_upload']       = DATE('Y-m-d');
                    $insert['id_campaign']      = $row['target_campaign'];
                    $insert['uploadcode']       = $uploadcode;
                    $insert['skip_reason']      = $skip_reason;

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);
                    array_push($insertData_skip, $str);
                    $inserted1++;
                }

            elseif ($main_product == 'ACS') : ## xsell ACS 
                $hp1_ori            = $row['field_9'];
                $hp1                = $this->phone_sensor->recognize($hp1_ori);

                ## Check if blacklisted phone_number
                if ($hp1 != '') {
                    $this->db->where('type', 'phonenumber');
                    $this->db->where('value', $this->db->escape_str($hp1));
                    $this->db->where('is_active', 1);
                    $qObj = $this->db->get('tb_blacklist');
                    if ($qObj->num_rows() > 0) {
                        $skip = 1;
                        $skip_reason = 'Blacklisted PhoneNumber: ' . $hp1;
                    } //this data will skiped
                }

                // Update Enddate Campaign
                if ($g < 1) {
                    $expiredcamp = $qArr[0]['field_64'];
                    $camp_target = $qArr[0]['target_campaign'];
                    $expiredcamp2 = $this->convertExcelToNormalDateTRP($expiredcamp);
                    $satu = $this->update_campaign($expiredcamp2, $camp_target);
                    $this->db->query($satu);
                }
                // End Update Campaign Enddate

                if ($skip == 0) {
                    ## Main Data ACS & ACT
                    $insert['fullname']            = $row['field_2'];
                    $insert['social_number']       = $row['field_5'];
                    // $insert['pob']                 = $row['field_4'];  
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_5']);
                    // $insert['email']               = $row['field_19'];
                    $insert['datainfo']            = $row['field_45'];
                    $insert['card_number_basic']   = $row['field_13'];
                    $insert['card_type']           = $row['field_14'];
                    // $insert['card_exp']            = $row['field_31'];
                    $insert['cif_no']              = $row['field_3'];
                    $insert['gender']              = $row['field_6'];
                    $insert['cnum']                = $row['field_4'];
                    $insert['dummy_id']            = $row['field_44'];
                    $insert['segment1']            = $row['field_42'];
                    $insert['segment2']            = $row['field_22'];
                    $insert['segment3']            = $row['field_43'];
                    $insert['cycle']               = $row['field_27'];

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_5'], '/');
                    // }

                    ## Data COP
                    $insert['status']              = $row['field_26'];
                    $insert['creditlimit']         = $row['field_30'];
                    $insert['max_loan']            = $row['field_31'];
                    $insert['loan1']               = $row['field_32'];
                    $insert['loan2']               = $row['field_33'];
                    $insert['loan3']               = $row['field_34'];

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_7'];
                    $insert['office_phone1_ori']   = $row['field_8'];
                    $insert['hp1_ori']             = $row['field_9'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Default Data
                    $insert['tgl_upload']          = DATE('Y-m-d');
                    $insert['id_campaign']         = $row['target_campaign'];
                    $insert['uploadcode']          = $uploadcode;

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_10'];
                    $insert['autodebet']           = $row['field_11'];

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect', $insert);

                    array_push($insertData, $str);
                    $inserted++;

                    ## XSEL DATA
                    $acs['cif_no']              = $row['field_3'];
                    $acs['id_campaign']         = $row['target_campaign'];
                    $acs['id_product']          = '48';
                    $acs['xsell_cardnumber']    = $row['field_13'];
                    $acs['xsell_cardtype']      = $row['field_14'];
                    $acs['xsell_cardowner']     = $row['field_2'];

                    $acs['xsell_offer1']        = $row['field_16'];
                    $acs['xsell_offer2']        = $row['field_17'];

                    // $acs['xsell_cardsup1']      = $row['field_57'];
                    // $acs['xsell_cardsup2']      = $row['field_58'];
                    // $acs['xsell_cardsup3']      = $row['field_59'];
                    // $acs['xsell_cardsup4']      = $row['field_60'];

                    // $acs['xsell_cardsupname1']  = $row['field_53'];
                    // $acs['xsell_cardsupname2']  = $row['field_54'];
                    // $acs['xsell_cardsupname3']  = $row['field_55'];
                    // $acs['xsell_cardsupname4']  = $row['field_56'];

                    if ($row['field_40'] != '') {
                        $tmp_imp = explode(';', $row['field_40']);
                        $acs['xsell_cardxsell'] = json_encode($tmp_imp);
                    }

                    $acs['uploadcode']          = $uploadcode;

                    $str_xsell = "";
                    $str_xsell = $this->db->insert_string('tb_xsell', $acs);
                    // var_dump($str);
                    array_push($insertData_sup, $str_xsell);
                } else {
                    ## Main Data ACS & ACT
                    $insert['fullname']            = $row['field_2'];
                    $insert['social_number']       = $row['field_5'];
                    // $insert['pob']                 = $row['field_4'];  
                    // $insert['dob']                 = $this->convertExcelToNormalDateTRP($row['field_5']);
                    // $insert['email']               = $row['field_19'];
                    $insert['datainfo']            = $row['field_45'];
                    $insert['card_number_basic']   = $row['field_13'];
                    $insert['card_type']           = $row['field_14'];
                    // $insert['card_exp']            = $row['field_31'];
                    $insert['cif_no']              = $row['field_3'];
                    $insert['gender']              = $row['field_6'];
                    $insert['cnum']                = $row['field_4'];
                    $insert['dummy_id']            = $row['field_44'];
                    $insert['segment1']            = $row['field_42'];
                    $insert['segment2']            = $row['field_22'];
                    $insert['segment3']            = $row['field_43'];
                    $insert['cycle']               = $row['field_27'];

                    ## Try to Fix DOB
                    // if( substr($insert['dob'],0,4) >= date('Y') || $insert['dob'] == '0000-00-00' ){
                    //     $insert['dob']             =  $this->convertTextDatefop($row['field_5'], '/');
                    // }

                    ## Data COP
                    $insert['status']              = $row['field_26'];
                    $insert['creditlimit']         = $row['field_30'];
                    $insert['max_loan']            = $row['field_31'];
                    $insert['loan1']               = $row['field_32'];
                    $insert['loan2']               = $row['field_33'];
                    $insert['loan3']               = $row['field_34'];

                    ## Phone Number
                    $insert['home_phone1_ori']     = $row['field_7'];
                    $insert['office_phone1_ori']   = $row['field_8'];
                    $insert['hp1_ori']             = $row['field_9'];
                    $insert['home_phone1']         = $this->phone_sensor->recognize($insert['home_phone1_ori']);
                    $insert['office_phone1']       = $this->phone_sensor->recognize($insert['office_phone1_ori']);
                    $insert['hp1']                 = $this->phone_sensor->recognize($insert['hp1_ori']);

                    ## Default Data
                    $insert['tgl_upload']          = DATE('Y-m-d');
                    $insert['id_campaign']         = $row['target_campaign'];
                    $insert['uploadcode']          = $uploadcode;

                    ## Data for Verification
                    $insert['bill_statement']      = $row['field_10'];
                    $insert['autodebet']           = $row['field_11'];

                    $str = "";
                    $str = $this->db->insert_string('tb_prospect_skip', $insert);

                    array_push($insertData_skip, $str);
                    $inserted1++;
                }
            endif;
            $g++;
        } ## End foreach

        ### TRANSAKSI FOP
        $trxInsert = 0; ## declare jumlah insert trx 
        if ($main_product == 'COP') : ## COP
            foreach ($qArr as $row) {
                $fop = array();
                if ($row['field_51']) ## Jika tipe kartu true 
                {
                    ##FOP Data
                    $fop['cif_no']              = $row['field_3'];
                    $fop['cnum']                = $row['field_4'];
                    $fop['id_campaign']         = $row['target_campaign'];
                    $fop['card_basic']          = $row['field_15'];
                    $fop['trx_card']            = $row['field_17'];
                    $fop['trx_cardtype']        = $row['field_51'];
                    $fop['trx_reff']            = $row['field_32'];
                    $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_35']);
                    $fop['trx_amount']          = $row['field_33'];
                    $fop['trx_description']     = $row['field_34'];
                    $fop['trx_countcard']       = $row['field_46'];
                    $fop['uploadcode']          = $uploadcode;

                    $str = "";
                    $str = $this->db->insert_string('tb_trxdetail', $fop);
                    array_push($insertData_trx, $str);
                    $trxInsert++;
                }
            } ## End foreach
        elseif ($main_product == 'FOP') : ## FOP
            foreach ($qArr as $row) {
                $fop = array();
                if ($row['field_53']) ## Jika tipe kartu true 
                {
                    ##FOP Data
                    $fop['cif_no']              = $row['field_3'];
                    $fop['cnum']                = $row['field_4'];
                    $fop['id_campaign']         = $row['target_campaign'];
                    $fop['card_basic']          = $row['field_20'];
                    $fop['trx_card']            = $row['field_22'];
                    // $fop['trx_cardtype']        = $row['field_53']; ##maping lama
                    $fop['trx_cardtype']        = $row['field_13']; ##maping baru
                    $fop['trx_reff']            = $row['field_23'];
                    $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_27']);
                    $fop['trx_amount']          = $row['field_24'];
                    $fop['trx_description']     = $row['field_26'];
                    $fop['trx_countcard']       = $row['field_48'];
                    $fop['uploadcode']          = $uploadcode;

                    $str = "";
                    $str = $this->db->insert_string('tb_trxdetail', $fop);
                    array_push($insertData_trx, $str);
                    $trxInsert++;
                }
            } ## End foreach
        elseif ($main_product == 'PL') : ## PL
            foreach ($qArr as $row) {
                $fop = array();
                if ($row['field_83']) ## Jika tipe kartu true 
                {
                    ##FOP Data
                    $fop['cif_no']              = $row['field_42'];
                    $fop['cnum']                = $row['field_3'];
                    $fop['id_campaign']         = $row['target_campaign'];
                    $fop['card_basic']          = $row['field_4'];
                    $fop['trx_card']            = $row['field_5'];
                    $fop['trx_cardtype']        = $row['field_83'];
                    $fop['trx_reff']            = $row['field_9'];
                    $fop['trx_date']            = $this->convertExcelToNormalDate($row['field_62']);
                    $fop['trx_amount']          = $row['field_58'];
                    $fop['trx_description']     = $row['field_61'];
                    $fop['trx_countcard']       = $row['field_79'];
                    $fop['uploadcode']          = $uploadcode;

                    $str = "";
                    $str = $this->db->insert_string('tb_trxdetail', $fop);
                    array_push($insertData_trx, $str);
                    $trxInsert++;
                }
            } ## End foreach
        endif;

        ## Start Bulk Insert
        $this->multiple_insert($insertData, 500);
        $this->multiple_insert($insertData_skip, 500);

        ## Detail trx FOP
        $this->multiple_insert($insertData_trx, 500);

        ## Detail trx SUP
        $this->multiple_insert($insertData_sup, 500);

        $return['inserted'] = $inserted;
        $return['dup'] = $inserted1;
        $return['uploadcode'] = $uploadcode;
        return $return;
    }
}
