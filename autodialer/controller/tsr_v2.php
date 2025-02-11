<?php

class Tsr_v2 extends Controller
{

    private $role;
    private $username;
    private $id_user;
    private $tb_prospect;


    public function __construct()
    {
        parent::Controller();
        $this->tb_prospect = 'tb_prospect';
        $this->load->model('admin/campaign_model', 'campaign_model', true);
        $this->load->model('admin/config_model', 'config_model', true);
        $this->load->model('tsr/callcode_model', 'callcode_model', true);
        $this->load->model('tsr/product_model', 'product_model', true);
        $this->load->model('tsr/prospect_model', 'prospect_model', true);
        $this->load->model('tsr/reminder_model', 'reminder_model', true);
        $this->load->model('tsr/notelp_model', 'notelp_model', true);
        $this->load->model('auth_model', 'auth_model', true);

        if (@$_SESSION["role"] != "tsr") {
            redirect('login');
        }
        $this->auth_model->check_changepass();

        $this->role = $_SESSION["role"];
        $this->username = $_SESSION["username"];
        $this->id_user = $_SESSION["id_user"];
        $local_number = $this->config_model->get_list_setup('id_call_setup="1"');
        $ip_pabx = $this->config_model->get_list_setup('id_call_setup="2"');
        $this->local_number = $local_number[0]['value'];
        $this->ip_pabx = $ip_pabx[0]['value'];
    }

    public function index()
    {
        redirect('tsr_v2/main');
    }

    public function main($id_campaign = '')
    {
        //$this->output->enable_profiler(TRUE);
        // die('haha');
        // Second Push
        $data["txt_title"] = "Tele Dashboard";
        $post = @$_POST["post"];

        $this->load->model('misc/misc_model');
        $this->load->model('offer_model');

        // predefine
        $id_prospect = $this->uri->segment(4);
        $id_product = $this->uri->segment(5);
        $id_user = $_SESSION['id_user'];

        $save_product = @$_POST["save_product"];
        $close_prospect = @$_POST["close_prospect"];
        $prospect = null;

        ##set call time agent
        $this->db->select('*');
        $this->db->from('tb_set_time_call');

        if (date('d', time()) == date('d', strtotime('Saturday'))) {
            $this->db->where('start_time_saturday <=', DATE('H:i:s'));
            $this->db->where('end_time_saturday >=', DATE('H:i:s'));
        } else {
            $this->db->where('star_time <=', DATE('H:i:s'));
            $this->db->where('end_time >=', DATE('H:i:s'));
        }

        //$this->db->where('break_time >', DATE('H:i:s'));
        $query = $this->db->get();
        //  var_dump($query); 
        //echo $this->db->last_query(); die();
        $row = $query->num_rows();
        $data['set_time_call'] = $row;


        //check if user choose prospect
        if (!empty($id_prospect)) {

            ## Data Campaign
            $camps = $this->misc_model->get_tableDataById('tb_campaign', $id_campaign, 'id_campaign');
            $is_priority = $this->misc_model->get_tableDataById('tb_prospect', $id_prospect, 'id_prospect');

            if ($is_priority['is_priority'] == 1) {
                // if ($camps['campaign_product'] == 46 || $camps['campaign_product'] == 40) { ## ByPass product COP by Request
                //getcustomer
                $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_blocked IS NULL AND tc.enddate >= CURDATE() AND tp.`is_priority`=1");
                // } else {
                //getcustomer
                // $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_blocked IS NULL AND tp.`is_priority`=1 AND tc.published = 1");
                // }
            } else {

                if ($camps['campaign_product'] == 46) { ## ByPass product COP by Request
                    //getcustomer
                    $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_blocked IS NULL AND tc.begindate <= CURDATE() AND tc.enddate > CURDATE()");
                } else {
                    //getcustomer
                    $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_blocked IS NULL AND tc.begindate <= CURDATE() AND tc.enddate > CURDATE() AND tc.published = 1");
                }
            }


            // echo $this->db->last_query(); die();
            //no customer found;
            if (empty($prospect)) {
                redirect('/tsr_v2/main');
            }
            $data["prospect"] = $prospect;
            $data["prospect"] = $data["prospect"][0];
            $data['last_id_callcode'] = $data["prospect"]['last_id_callcode'];
            $data['last_call_weight'] = $data['last_id_callcode'] != '0' ? $this->misc_model->get_tableDataById('tb_callcode', $data['last_id_callcode'], 'id_callcode', 'weight') : '0';
            //var_dump($data['last_id_callcode'], $data['last_call_weight']);
            //var_dump($data["prospect"]['multiproduct']);

            if ($camps['multiproduct'] != '1') :
                if ($camps['campaign_product'] == 47 || $camps['campaign_product'] == 48 || $camps['campaign_product'] == 50 || $camps['campaign_product'] == 51 || $camps['campaign_product'] == 52 || $camps['campaign_product'] == 53) {
                    ##Data OFFER
                    $data_cif = $data["prospect"]['cif_no'];
                    $data_card = $data["prospect"]['card_number_basic'];
                    $sqlo = "select tp.*, toff.id_campaign, toff.cif_no, toff.xsell_cardxsell from tb_prospect tp, tb_xsell toff
                            where tp.id_prospect='$id_prospect'
                            and toff.cif_no='$data_cif'
                            and toff.xsell_cardnumber='$data_card'
                            and tp.id_campaign = toff.id_campaign";
                    //die($sqlo);
                    $off_cek = $this->db->query($sqlo);
                    $rowoff = $off_cek->row_array();

                    $this->load->model('offer_model');
                    $offers = $this->offer_model->get_offer_byCardNum($data['prospect']['cif_no'], $id_campaign, $data['prospect']['card_number_basic'], $id_prospect);
                    $data["offers"] = $offers;
                }

                if ($camps['campaign_product'] == 46) {
                    ## Get Refresh
                    $this->load->model('cop_model');
                    $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);

                    ## Get Installment Tree
                    if (strpos($prospect[0]['name'], 'NTK') > 0) {
                        $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}' OR status='TYPE 28')");
                    } else if (strpos($prospect[0]['name'], 'DAP') > 0) {
                        $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                    } else {
                        $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}')");
                    }
                } elseif ($camps['campaign_product'] == 44) {
                    $this->load->model('admin/msc_model');
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}' AND type = 'FP' AND is_active = 1");
                } elseif ($camps['campaign_product'] == 57) {
                    $this->load->model('admin/msc_model');
                    ## Get Refresh
                    $this->load->model('cop_model');
                    $data['refreshbagfop'] = $this->cop_model->get_datarefreshfop($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                    // echo $this->db->last_query();
                    // die();
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}' AND type = 'FP' AND is_active = 1");
                } elseif ($camps['campaign_product'] == 58) {
                    $this->load->model('admin/msc_model');
                    ## Get Refresh
                    $this->load->model('cop_model');
                    $data['refreshbagfop'] = $this->cop_model->get_datarefreshfop($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                    // echo $this->db->last_query();
                    // die();
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}' AND type = 'FP' AND is_active = 1");
                } elseif ($camps['campaign_product'] == 32) {
                    $this->load->model('admin/msc_model');
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}'");
                } elseif ($camps['campaign_product'] == 55) {
                    $this->load->model('admin/msc_model');
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}'");
                    ## Get Refresh
                    $this->load->model('cop_model');
                    $data['refreshbag'] = $this->cop_model->get_datarefreshcpil($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                } else {
                    $data['refreshbag'] = array();
                    $data['list_productcode'] = array();
                }
            elseif ($camps['multiproduct'] == '1') :
                ##Data OFFER
                $data_cif = $data["prospect"]['cif_no'];
                $data_card = $data["prospect"]['card_number_basic'];
                $sqlo = "select tp.*, toff.id_campaign, toff.cif_no, toff.xsell_cardxsell from tb_prospect tp, tb_xsell toff
                        where tp.id_prospect='$id_prospect'
                        and toff.cif_no='$data_cif'
                        and toff.xsell_cardnumber='$data_card'
                        and tp.id_campaign = toff.id_campaign";
                //die($sqlo);
                $off_cek = $this->db->query($sqlo);
                $rowoff = $off_cek->row_array();

                $this->load->model('offer_model');
                $offers = $this->offer_model->get_offer_byCardNum($data['prospect']['cif_no'], $id_campaign, $data['prospect']['card_number_basic'], $id_prospect);
                $data["offers"] = $offers;
                // echo $this->db->last_query();exit();
                $product_xsell = json_decode($data['offers'][0]['xsell_cardxsell'], true);
                $main_product = $camps['campaign_product'];

                // onmra: 106
                $loop_refreshbag = 0;
                foreach ($product_xsell as $xsell) {
                    $loop_refreshbag++;
                    if ($xsell == 'COP') {
                        if ($main_product == '46') {
                            ## Get Refresh
                            $this->load->model('cop_model');
                            if ($loop_refreshbag == 1) {
                                $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                            }
                            ## Get Installment Tree
                            if (strpos($prospect[0]['name'], 'NTK') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}' OR status='TYPE 28')");
                            } else if (strpos($prospect[0]['name'], 'DAP') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                            } else {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}')");
                            }
                            // var_dump($data['list_productcode']);

                            $this->load->model('admin/msc_model');
                            $data["list_productcode_FP"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status2']}' AND type = 'FP' AND is_active = 1");
                        } else if ($main_product == '48') {
                            ## Get Refresh
                            $this->load->model('cop_model');
                            if ($loop_refreshbag == 1) {
                                $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                            }
                            ## Get Installment Tree
                            if (strpos($prospect[0]['name'], 'NTK') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}' OR status='TYPE 28')");
                            } else if (strpos($prospect[0]['name'], 'DAP') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                            } else {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}')");
                            }
                        }
                    } elseif ($xsell == 'FP') {
                        if ($main_product == '44') {
                            $this->load->model('admin/msc_model');
                            $data["list_productcode_FP"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}' AND type = 'FP' AND is_active = 1"); //echo $this->db->last_query();exit();

                            ## Get Refresh
                            $this->load->model('cop_model');
                            if ($loop_refreshbag == 1) {
                                $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                            }
                            ## Get Installment Tree
                            if (strpos($prospect[0]['name'], 'NTK') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status2']}' OR status='TYPE 28')");
                            } else if (strpos($prospect[0]['name'], 'DAP') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                            } else {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status2']}')");
                            }
                        }
                        // else {
                        //     $this->load->model('admin/msc_model');
                        //     $data["list_productcode_FP"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status2']}' AND type = 'FP' AND is_active = 1");
                        // }
                    } elseif ($xsell == 'PL') {
                        if ($main_product == '32') {
                            $this->load->model('admin/msc_model');
                            $data["list_productcode_PL"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}'");

                            $this->load->model('admin/msc_model');
                            $data["list_productcode_FP"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status2']}' AND type = 'FP' AND is_active = 1");
                        }
                        // else {
                        //     $this->load->model('admin/msc_model');
                        //     $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status2']}'");
                        // }
                    } else {
                        if ($loop_refreshbag == 1) {
                            $data['refreshbag'] = array();
                        }
                        $data['list_productcode'] = array();
                    }
                }
            endif;





            /*if($camps['campaign_product'] == 46){
                ## Get Refresh
                $this->load->model('cop_model');
                $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                
                ## Get Installment Tree
                if(strpos($prospect[0]['name'], 'NTK') > 0){
                    $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}' OR status='TYPE 28')");
                } else if(strpos($prospect[0]['name'], 'DAP') > 0){
                    $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                } else {
                    $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}')");
                }
            } else{
                $data['refreshbag'] = array();
                $data['list_productcode'] = array();
            }
            if($camps['campaign_product'] == 47 || $camps['campaign_product'] == 48 || $camps['campaign_product'] == 50 || $camps['campaign_product'] == 51 || $camps['campaign_product'] == 52 || $camps['campaign_product'] == 53){
                ##Data OFFER
                $data_cif = $data["prospect"]['cif_no'];
                $data_card = $data["prospect"]['card_number_basic'];
                $sqlo = "select tp.*, toff.id_campaign, toff.cif_no, toff.xsell_cardxsell from tb_prospect tp, tb_xsell toff
                        where tp.id_prospect='$id_prospect'
                        and toff.cif_no='$data_cif'
                        and toff.xsell_cardnumber='$data_card'
                        and tp.id_campaign = toff.id_campaign";
                    //die($sqlo);
                $off_cek = $this->db->query($sqlo);
                $rowoff=$off_cek->row_array();
                
                $this->load->model('offer_model');
                $offers = $this->offer_model->get_offer_byCardNum($data['prospect']['cif_no'],$id_campaign, $data['prospect']['card_number_basic'], $id_prospect);
                $data["offers"] = $offers;
                
            }*/
            //$dataoffer = $offer[0]['off_card'];
            //$datacountoffer = $prospect[0]['supplement_card'];
            //var_dump($offers);die();
            $data['next_prospect'] = ""; //$this->prospect_model->get_list_prospect("id_prospect>'$id_prospect' AND id_campaign='$id_campaign' AND is_agree!=1 AND is_close!=1 AND id_tsr='" . $this->id_user . "'", 1, 1);
            $data['prev_prospect'] = ""; //$this->prospect_model->get_list_prospect("id_prospect<'$id_prospect' AND id_campaign='$id_campaign' AND is_agree!=1 AND is_close!=1 AND id_tsr='" . $this->id_user . "'", 1, 1, 'id_prospect DESC');
        }

        //close prospect
        if ($close_prospect) {
            $this->prospect_model->close_prospect($id_prospect);
        }

        $data['is_agree'] = $this->prospect_model->get_list_agree_prospect('tp.id_prospect="' . $id_prospect . '"');
        //var_dump($offer[0]['off_card']);die();

        $credit_shield = $prospect[0]['credit_shield'];
        $supplement_card = $prospect[0]['supplement_card'];

        //product
        $data['product'] = $this->list_product($id_prospect, $id_campaign, $credit_shield, $supplement_card);

        $data['ip_pabx'] = $this->ip_pabx;
        $data['local_number'] = $this->local_number;

        $data['list_status'] = $this->prospect_model->get_list_status();
        $data["reminder"] = $this->reminder();

        //$data["agree_prospect"] = $this->agree_prospect($id_campaign);
        $data["last_callhistory"] = $this->last_callhistory($id_prospect, 10);

        //$data["list_campaign"] = $this->campaign_model->get_list_campaign('begindate<=now() AND enddate>=now() AND published=1');
        $data["list_campaign"] = $this->campaign_model->get_list_campaign_tele('tc.begindate<=now() AND tc.enddate>now() AND tc.published=1');
        //echo $this->db->last_query();
        //die();

        //$data['last_remark'] = $this->prospect_model->get_prospect_last_remark($id_prospect);
        $data['last_remark'] = $this->prospect_model->get_prospect_list_remark($id_prospect, 10);
        //var_dump($data['status_ver']);die();

        $data["id_prospect"] = @$id_prospect;
        $data["id_campaign"] = @$id_campaign;
        $data["id_product"] = @$id_product;

        ##script tele
        $sql = "select * from tb_product where published=1";

        $q = $this->db->query($sql);

        $row_script = array();

        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;


        ##script tele 2	
        /*
		$sql1 = "select * from tb_script group by jenis_dokumen";       
        $q1 = $this->db->query($sql1);

        $row_script1 = array();

        if($q1->num_rows() > 0){
        	foreach($q1->result_array() as $row1)
        	{
       		 	$row_script1[] = $row1;
       		}
       	}

        $data['scripts1'] = $row_script1;
*/
        $this->load->model('dbadmin/config_model', 'config_model');
        $data['maxcall_enable'] = $this->config_model->get_list_setup('id_call_setup="10"');
        $data['maxcall_enable'] = $data['maxcall_enable'][0]['value'];

        $data['maxcall_perday'] = $this->config_model->get_list_setup('id_call_setup="11"');
        $data['maxcall_permonth'] = $this->config_model->get_list_setup('id_call_setup="12"');
        $data['pre_validation'] = $this->config_model->get_list_setup('id_call_setup="16"');

        $data['maxcall_permonth']  = (@$data['maxcall_permonth'][0]['value'] * 1) + (@$data['prospect']['extend_call'] * 1);

        ## Calltrack Model
        $this->load->model('calltrack_model');
        $data['calltrackModel'] = $this->calltrack_model;

        ## Misc Model
        $data['miscModel'] = $this->misc_model;

        ## Get Script
        /*
        $this->db->where('is_active', 1);
        $qObj = $this->db->get('tb_scripts'); 
        $qArr = $qObj->num_rows() > 0 ? $qObj->result_array() : array(); 
        
        $data['scripts'] = $qArr;
*/
        //$this->output->enable_profiler(TRUE);
        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/main_v2', $data);
        $this->load->view('tsr/footer', $data);
        $this->load->view('flashmessage');
    }

    public function main_r1($id_campaign = '')
    {
        //$this->output->enable_profiler(TRUE);

        $data["txt_title"] = "Tele Dashboard";
        $post = @$_POST["post"];

        $this->load->model('misc/misc_model');
        $this->load->model('offer_model');

        // predefine
        $id_prospect = $this->uri->segment(4);
        $id_product = $this->uri->segment(5);
        $id_user = $_SESSION['id_user'];

        $save_product = @$_POST["save_product"];
        $close_prospect = @$_POST["close_prospect"];
        $prospect = null;

        ##set call time agent
        $this->db->select('*');
        $this->db->from('tb_set_time_call');

        if (date('d', time()) == date('d', strtotime('Saturday'))) {
            $this->db->where('start_time_saturday <=', DATE('H:i:s'));
            $this->db->where('end_time_saturday >=', DATE('H:i:s'));
        } else {
            $this->db->where('star_time <=', DATE('H:i:s'));
            $this->db->where('end_time >=', DATE('H:i:s'));
        }

        //$this->db->where('break_time >', DATE('H:i:s'));
        $query = $this->db->get();
        //  var_dump($query); 
        //echo $this->db->last_query(); die();
        $row = $query->num_rows();
        $data['set_time_call'] = $row;


        //check if user choose prospect
        if (!empty($id_prospect)) {

            ## Data Campaign
            $camps = $this->misc_model->get_tableDataById('tb_campaign', $id_campaign, 'id_campaign');
            $is_priority = $this->misc_model->get_tableDataById('tb_prospect', $id_prospect, 'id_prospect');

            if ($is_priority['is_priority'] == 1) {
                // if ($camps['campaign_product'] == 46 || $camps['campaign_product'] == 40) { ## ByPass product COP by Request
                //getcustomer
                $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_blocked IS NULL AND tc.enddate >= CURDATE() AND tp.`is_priority`=1");
                // } else {
                //getcustomer
                // $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_blocked IS NULL AND tp.`is_priority`=1 AND tc.published = 1");
                // }
            } else {

                if ($camps['campaign_product'] == 46) { ## ByPass product COP by Request
                    //getcustomer
                    $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_blocked IS NULL AND tc.begindate <= CURDATE() AND tc.enddate > CURDATE()");
                } else {
                    //getcustomer
                    $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_blocked IS NULL AND tc.begindate <= CURDATE() AND tc.enddate > CURDATE() AND tc.published = 1");
                }
            }


            // echo $this->db->last_query(); die();
            //no customer found;
            if (empty($prospect)) {
                redirect('/tsr_v2/main');
            }
            $data["prospect"] = $prospect;
            $data["prospect"] = $data["prospect"][0];
            $data['last_id_callcode'] = $data["prospect"]['last_id_callcode'];
            $data['last_call_weight'] = $data['last_id_callcode'] != '0' ? $this->misc_model->get_tableDataById('tb_callcode', $data['last_id_callcode'], 'id_callcode', 'weight') : '0';
            //var_dump($data['last_id_callcode'], $data['last_call_weight']);
            //var_dump($data["prospect"]['multiproduct']);

            if ($camps['multiproduct'] != '1') :
                if ($camps['campaign_product'] == 47 || $camps['campaign_product'] == 48 || $camps['campaign_product'] == 50 || $camps['campaign_product'] == 51 || $camps['campaign_product'] == 52 || $camps['campaign_product'] == 53) {
                    ##Data OFFER
                    $data_cif = $data["prospect"]['cif_no'];
                    $data_card = $data["prospect"]['card_number_basic'];
                    $sqlo = "select tp.*, toff.id_campaign, toff.cif_no, toff.xsell_cardxsell from tb_prospect tp, tb_xsell toff
                            where tp.id_prospect='$id_prospect'
                            and toff.cif_no='$data_cif'
                            and toff.xsell_cardnumber='$data_card'
                            and tp.id_campaign = toff.id_campaign";
                    //die($sqlo);
                    $off_cek = $this->db->query($sqlo);
                    $rowoff = $off_cek->row_array();

                    $this->load->model('offer_model');
                    $offers = $this->offer_model->get_offer_byCardNum($data['prospect']['cif_no'], $id_campaign, $data['prospect']['card_number_basic'], $id_prospect);
                    $data["offers"] = $offers;
                }

                if ($camps['campaign_product'] == 46) {
                    ## Get Refresh
                    $this->load->model('cop_model');
                    $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);

                    ## Get Installment Tree
                    if (strpos($prospect[0]['name'], 'NTK') > 0) {
                        $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}' OR status='TYPE 28')");
                    } else if (strpos($prospect[0]['name'], 'DAP') > 0) {
                        $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                    } else {
                        $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}')");
                    }
                } elseif ($camps['campaign_product'] == 44) {
                    $this->load->model('admin/msc_model');
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}' AND type = 'FP' AND is_active = 1");
                } elseif ($camps['campaign_product'] == 57) {
                    $this->load->model('admin/msc_model');
                    ## Get Refresh
                    $this->load->model('cop_model');
                    $data['refreshbagfop'] = $this->cop_model->get_datarefreshfop($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                    // echo $this->db->last_query();
                    // die();
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}' AND type = 'FP' AND is_active = 1");
                } elseif ($camps['campaign_product'] == 58) {
                    $this->load->model('admin/msc_model');
                    ## Get Refresh
                    $this->load->model('cop_model');
                    $data['refreshbagfop'] = $this->cop_model->get_datarefreshfop($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                    // echo $this->db->last_query();
                    // die();
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}' AND type = 'FP' AND is_active = 1");
                } elseif ($camps['campaign_product'] == 32) {
                    $this->load->model('admin/msc_model');
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}'");
                } elseif ($camps['campaign_product'] == 55) {
                    $this->load->model('admin/msc_model');
                    $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}'");
                    ## Get Refresh
                    $this->load->model('cop_model');
                    $data['refreshbag'] = $this->cop_model->get_datarefreshcpil($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                } else {
                    $data['refreshbag'] = array();
                    $data['list_productcode'] = array();
                }
            elseif ($camps['multiproduct'] == '1') :
                ##Data OFFER
                $data_cif = $data["prospect"]['cif_no'];
                $data_card = $data["prospect"]['card_number_basic'];
                $sqlo = "select tp.*, toff.id_campaign, toff.cif_no, toff.xsell_cardxsell from tb_prospect tp, tb_xsell toff
                        where tp.id_prospect='$id_prospect'
                        and toff.cif_no='$data_cif'
                        and toff.xsell_cardnumber='$data_card'
                        and tp.id_campaign = toff.id_campaign";
                //die($sqlo);
                $off_cek = $this->db->query($sqlo);
                $rowoff = $off_cek->row_array();

                $this->load->model('offer_model');
                $offers = $this->offer_model->get_offer_byCardNum($data['prospect']['cif_no'], $id_campaign, $data['prospect']['card_number_basic'], $id_prospect);
                $data["offers"] = $offers;
                // echo $this->db->last_query();exit();
                $product_xsell = json_decode($data['offers'][0]['xsell_cardxsell'], true);
                $main_product = $camps['campaign_product'];

                // onmra: 106
                $loop_refreshbag = 0;
                foreach ($product_xsell as $xsell) {
                    $loop_refreshbag++;
                    if ($xsell == 'COP') {
                        if ($main_product == '46') {
                            ## Get Refresh
                            $this->load->model('cop_model');
                            if ($loop_refreshbag == 1) {
                                $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                            }
                            ## Get Installment Tree
                            if (strpos($prospect[0]['name'], 'NTK') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}' OR status='TYPE 28')");
                            } else if (strpos($prospect[0]['name'], 'DAP') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                            } else {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}')");
                            }
                            // var_dump($data['list_productcode']);

                            $this->load->model('admin/msc_model');
                            $data["list_productcode_FP"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status2']}' AND type = 'FP' AND is_active = 1");
                        } else if ($main_product == '48') {
                            ## Get Refresh
                            $this->load->model('cop_model');
                            if ($loop_refreshbag == 1) {
                                $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                            }
                            ## Get Installment Tree
                            if (strpos($prospect[0]['name'], 'NTK') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}' OR status='TYPE 28')");
                            } else if (strpos($prospect[0]['name'], 'DAP') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                            } else {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}')");
                            }
                        }
                    } elseif ($xsell == 'FP') {
                        if ($main_product == '44') {
                            $this->load->model('admin/msc_model');
                            $data["list_productcode_FP"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}' AND type = 'FP' AND is_active = 1"); //echo $this->db->last_query();exit();

                            ## Get Refresh
                            $this->load->model('cop_model');
                            if ($loop_refreshbag == 1) {
                                $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                            }
                            ## Get Installment Tree
                            if (strpos($prospect[0]['name'], 'NTK') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status2']}' OR status='TYPE 28')");
                            } else if (strpos($prospect[0]['name'], 'DAP') > 0) {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                            } else {
                                $data['list_productcode_cop'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status2']}')");
                            }
                        }
                        // else {
                        //     $this->load->model('admin/msc_model');
                        //     $data["list_productcode_FP"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status2']}' AND type = 'FP' AND is_active = 1");
                        // }
                    } elseif ($xsell == 'PL') {
                        if ($main_product == '32') {
                            $this->load->model('admin/msc_model');
                            $data["list_productcode_PL"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status']}'");

                            $this->load->model('admin/msc_model');
                            $data["list_productcode_FP"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status2']}' AND type = 'FP' AND is_active = 1");
                        }
                        // else {
                        //     $this->load->model('admin/msc_model');
                        //     $data["list_productcode"] = $this->msc_model->get_list_msc("status='{$data["prospect"]['status2']}'");
                        // }
                    } else {
                        if ($loop_refreshbag == 1) {
                            $data['refreshbag'] = array();
                        }
                        $data['list_productcode'] = array();
                    }
                }
            endif;





            /*if($camps['campaign_product'] == 46){
                ## Get Refresh
                $this->load->model('cop_model');
                $data['refreshbag'] = $this->cop_model->get_datarefresh($data["prospect"]['cif_no'], $data["prospect"]['card_number_basic'], $data["prospect"]['refreshcode']);
                
                ## Get Installment Tree
                if(strpos($prospect[0]['name'], 'NTK') > 0){
                    $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}' OR status='TYPE 28')");
                } else if(strpos($prospect[0]['name'], 'DAP') > 0){
                    $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='DAP')");
                } else {
                    $data['list_productcode'] = $this->cop_model->get_productcode_bytype('COP', "(status='{$prospect[0]['status']}')");
                }
            } else{
                $data['refreshbag'] = array();
                $data['list_productcode'] = array();
            }
            if($camps['campaign_product'] == 47 || $camps['campaign_product'] == 48 || $camps['campaign_product'] == 50 || $camps['campaign_product'] == 51 || $camps['campaign_product'] == 52 || $camps['campaign_product'] == 53){
                ##Data OFFER
                $data_cif = $data["prospect"]['cif_no'];
                $data_card = $data["prospect"]['card_number_basic'];
                $sqlo = "select tp.*, toff.id_campaign, toff.cif_no, toff.xsell_cardxsell from tb_prospect tp, tb_xsell toff
                        where tp.id_prospect='$id_prospect'
                        and toff.cif_no='$data_cif'
                        and toff.xsell_cardnumber='$data_card'
                        and tp.id_campaign = toff.id_campaign";
                    //die($sqlo);
                $off_cek = $this->db->query($sqlo);
                $rowoff=$off_cek->row_array();
                
                $this->load->model('offer_model');
                $offers = $this->offer_model->get_offer_byCardNum($data['prospect']['cif_no'],$id_campaign, $data['prospect']['card_number_basic'], $id_prospect);
                $data["offers"] = $offers;
                
            }*/
            //$dataoffer = $offer[0]['off_card'];
            //$datacountoffer = $prospect[0]['supplement_card'];
            //var_dump($offers);die();
            $data['next_prospect'] = ""; //$this->prospect_model->get_list_prospect("id_prospect>'$id_prospect' AND id_campaign='$id_campaign' AND is_agree!=1 AND is_close!=1 AND id_tsr='" . $this->id_user . "'", 1, 1);
            $data['prev_prospect'] = ""; //$this->prospect_model->get_list_prospect("id_prospect<'$id_prospect' AND id_campaign='$id_campaign' AND is_agree!=1 AND is_close!=1 AND id_tsr='" . $this->id_user . "'", 1, 1, 'id_prospect DESC');
        }

        //close prospect
        if ($close_prospect) {
            $this->prospect_model->close_prospect($id_prospect);
        }

        $data['is_agree'] = $this->prospect_model->get_list_agree_prospect('tp.id_prospect="' . $id_prospect . '"');
        //var_dump($offer[0]['off_card']);die();

        $credit_shield = $prospect[0]['credit_shield'];
        $supplement_card = $prospect[0]['supplement_card'];

        //product
        $data['product'] = $this->list_product($id_prospect, $id_campaign, $credit_shield, $supplement_card);

        $data['ip_pabx'] = $this->ip_pabx;
        $data['local_number'] = $this->local_number;

        $data['list_status'] = $this->prospect_model->get_list_status();
        $data["reminder"] = $this->reminder();

        //$data["agree_prospect"] = $this->agree_prospect($id_campaign);
        $data["last_callhistory"] = $this->last_callhistory($id_prospect, 10);

        //$data["list_campaign"] = $this->campaign_model->get_list_campaign('begindate<=now() AND enddate>=now() AND published=1');
        $data["list_campaign"] = $this->campaign_model->get_list_campaign_tele('tc.begindate<=now() AND tc.enddate>now() AND tc.published=1');
        //echo $this->db->last_query();
        //die();

        //$data['last_remark'] = $this->prospect_model->get_prospect_last_remark($id_prospect);
        $data['last_remark'] = $this->prospect_model->get_prospect_list_remark($id_prospect, 10);
        //var_dump($data['status_ver']);die();

        $data["id_prospect"] = @$id_prospect;
        $data["id_campaign"] = @$id_campaign;
        $data["id_product"] = @$id_product;

        ##script tele
        $sql = "select * from tb_product where published=1";

        $q = $this->db->query($sql);

        $row_script = array();

        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;


        ##script tele 2	
        /*
		$sql1 = "select * from tb_script group by jenis_dokumen";       
        $q1 = $this->db->query($sql1);

        $row_script1 = array();

        if($q1->num_rows() > 0){
        	foreach($q1->result_array() as $row1)
        	{
       		 	$row_script1[] = $row1;
       		}
       	}

        $data['scripts1'] = $row_script1;
*/
        $this->load->model('dbadmin/config_model', 'config_model');
        $data['maxcall_enable'] = $this->config_model->get_list_setup('id_call_setup="10"');
        $data['maxcall_enable'] = $data['maxcall_enable'][0]['value'];

        $data['maxcall_perday'] = $this->config_model->get_list_setup('id_call_setup="11"');
        $data['maxcall_permonth'] = $this->config_model->get_list_setup('id_call_setup="12"');
        $data['pre_validation'] = $this->config_model->get_list_setup('id_call_setup="16"');

        $data['maxcall_permonth']  = (@$data['maxcall_permonth'][0]['value'] * 1) + (@$data['prospect']['extend_call'] * 1);

        ## Calltrack Model
        $this->load->model('calltrack_model');
        $data['calltrackModel'] = $this->calltrack_model;

        ## Misc Model
        $data['miscModel'] = $this->misc_model;

        ## Get Script
        /*
        $this->db->where('is_active', 1);
        $qObj = $this->db->get('tb_scripts');
        $qArr = $qObj->num_rows() > 0 ? $qObj->result_array() : array(); 
        
        $data['scripts'] = $qArr;
*/
        //$this->output->enable_profiler(TRUE);
        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/main_v2', $data);
        $this->load->view('tsr/footer', $data);
        $this->load->view('flashmessage');
    }

    function recall()
    {
        $data["txt_title"] = "Tele Dashboard";
        $post = @$_POST["post"];

        // predifine
        $id_prospect = $this->uri->segment(4);
        $id_product = $this->uri->segment(3);
        $loadform = $this->uri->segment(5);
        $id_user = $_SESSION['id_user'];
        if (!empty($id_prospect)) {
            $this->load->model('tsr/prospect_model', 'prospect_model', true);

            //getcustomer
            $prospect = $this->prospect_model->get_list_prospect(" id_prospect='$id_prospect' AND id_tsr='$id_user' AND is_agree='1' AND is_blocked IS NULL");


            if (empty($prospect)) {
                redirect('/spv/home');
            }
            $data["prospect"] = $prospect;
            $data["prospect"] = $data["prospect"][0];
            $data['id_prospect'] = $id_prospect;
            $data['id_product'] = $id_product;
            $data['loadform'] = $loadform;

            $data["last_callhistory"] = $this->last_callhistory($id_prospect, 10);

            $this->load->view('tsr/header', $data);
            $this->load->view('tsr/recall', $data);
            $this->load->view('tsr/footer', $data);
        }
    }

    public function verify_customer($id_prospect, $id_product = "", $data_campaign_type = "")
    {
        $data['txt_title'] = "Verifikasi Data";
        $data['id_prospect'] = $id_prospect;
        $data['id_product'] = $id_product;


        $sql = "select tp.*, tc.campaign_type from tb_prospect tp, tb_campaign tc
										  WHERE tp.id_prospect = $id_prospect
										  and tp.id_campaign=tc.id_campaign";
        //and tc.campaign_type=1";
        //die($sql);
        $q_cek = $this->db->query($sql);
        $row1 = $q_cek->row_array();
        $data_campaign_type = $row1['campaign_type'];

        //var_dump($data_campaign_type);



        //script tele
        $sql = "select * from tb_product where published=1";
        $q = $this->db->query($sql);



        $row_script = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;

        //script tele 2	

        $sql1 = "select * from tb_script group by jenis_dokumen";

        $q1 = $this->db->query($sql1);



        $row_script1 = array();
        if ($q1->num_rows() > 0) {
            foreach ($q1->result_array() as $row1) {
                $row_script1[] = $row1;
            }
        }

        $data['scripts1'] = $row_script1;

        //script tele 2	

        $sql = "select * from tb_prospect where id_prospect = '$id_prospect' ";
        //echo $sql ;
        $q = $this->db->query($sql);
        $data['hasil'] = $q->result_array();

        $this->load->view('tsr/header', $data);
        if ($data_campaign_type == 3) {
            $this->load->view('tsr/verify_incoming', $data);
        } else {
            $this->load->view('tsr/verify', $data);
        }
        $this->load->view('tsr/footer', $data);
    }


    public function verify_customer_1($id_prospect, $id_product = "")
    {
        //die('msk verify 1');
        $data['txt_title'] = "Verifikasi Data";
        $data['id_prospect'] = $id_prospect;
        $data['id_product'] = $id_product;

        //script tele
        $sql = "select * from tb_product where published=1";


        $q = $this->db->query($sql);

        $row_script = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;

        //script tele 2	

        $sql1 = "select * from tb_script group by jenis_dokumen";



        $q1 = $this->db->query($sql1);



        $row_script1 = array();
        if ($q1->num_rows() > 0) {
            foreach ($q1->result_array() as $row1) {
                $row_script1[] = $row1;
            }
        }

        $data['scripts1'] = $row_script1;

        //script tele 2	


        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/verify_1', $data);
        $this->load->view('tsr/footer', $data);
    }

    public function script123($id_product)
    {
        $sql = "select name, script_tele from tb_product where id_product=$id_product";
        $q = $this->db->query($sql);
        $row = $q->row_array();

        $data['row'] = $row;

        $this->load->view('tsr/script', $data);
    }

    public function window($id)
    {
        //echo $id;
        //die();
        $sql = "select * from tb_script where id = $id";

        //echo $sql;
        //die();

        $q = $this->db->query($sql);
        $row = $q->result_array();

        //var_dump($row);
        //die();

        $data['script'] = $row;

        $this->load->view('tsr/window', $data);
    }



    public function script($id_product)
    {
        //$sql = "select name, script_tele from tb_product where id_product=$id_product";		

        if ($id_product == 1) {
            $sql = "select * from tb_script where id_product=1";
        } elseif ($id_product == 2) {
            $sql = "select * from tb_script where id_product=2";
        } else {

            $sql = "select * from tb_script where id_product=3";
        }

        $q = $this->db->query($sql);
        //$row = $q->row_array();		
        $row = $q->result_array();

        //$data['row'] = $row;		
        $data['script'] = $row;

        $this->load->view('tsr/script', $data);
    }


    public function blockuser($id_prospect)
    {
        //block user automatic
        $sql = "update tb_prospect set is_blocked=1 where id_prospect=?";
        $this->db->query($sql, array($id_prospect));

        /*
			//update calltrack
			$data = array(
				"id_callcode" => 31,
				"id_prospect" => $id_prospect,
				"call_date" => date("Y-m-d"),
				"time" => date("H:i:s"),
				"id_user" => $this->id_user,
				"username" => $this->username
			);
//var_dump($data);
//die();
			$this->db->insert("tb_calltrack", $data);
      //$this->prospect_model->add_calltrack($id_prospect, '', $this->id_user, 31, '', date("Y-m-d"), date("H:i:s"), '', '', '', '', '', '', '', '');
*/

        $data['txt_title'] = "Blocked User";

        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/blocked', $data);
        $this->load->view('tsr/footer', $data);

        //redirect('tsr/main');
    }

    public function list_product($id_prospect, $id_campaign, $credit_shield, $supplement_card)
    {
        $data = "";
        $arrlist = $this->product_model->get_list_product($id_prospect, $id_campaign);

        $data["list"] = $arrlist;

        $data["id_campaign"] = $id_campaign;
        $data["id_prospect"] = $id_prospect;
        $data["credit_shield"] = $credit_shield;
        $data["supplement_card"] = $supplement_card;

        return $this->load->view('tsr/list_product', $data, true);
    }

    /*
    public function list_prospect($id_campaign="", $keyword="", $page="", $pageValue="") {

        $condition = $id_campaign ? ' tp.id_campaign=' . set_string_content($id_campaign) : '';
        $condition .= $keyword == 'null' || $keyword == '' ? '' : ' AND tp.fullname LIKE "%' . $keyword . '%"';
        $pageValue = $pageValue ? $pageValue : 1;
        $item_on_page = 10;
        $count = $this->prospect_model->get_count_assign($this->id_user, $condition, $keyword);
        $keyword = $keyword ? $keyword : 'null';
        $data["page"] = makePagingPage($item_on_page, $count, $pageValue, site_url() . '/tsr/list_prospect/' . $id_campaign . '/' . $keyword . '/page/', true, 'cont-prospect');
        $data["list"] = $this->prospect_model->get_list_assign($this->id_user, $condition, $keyword, $pageValue, $item_on_page);
        $data["id_campaign"] = $id_campaign;
        $data["list_header"] = $this->prospect_model->get_list_header_detail('', 1, 4, 'seq');
        $this->load->view('tsr/list_prospect', $data);
    }
*/
    /**
     * Get list prospect
     *
     * @param <type> $id_campaign
     * @param <type> $keyword
     * @param <type> $page
     * @param <type> $pageValue
     */
    public function list_prospectv3($id_campaign = "", $keyword = "null", $id_callcode = '0', $page = "", $pageValue = "")
    {

        $this->load->helper('cyrusenyx');
        $_SESSION['last_callcode'] = $id_callcode;
        $knds_agree = ($id_callcode == 52 ? ' AND tp.is_agree = 1' : ' AND tp.is_agree <> 1');
        //$this->output->enable_profiler(TRUE);
        $condition = $id_campaign ? ' tp.id_campaign=' . set_string_content($id_campaign) : '';
        $condition .= $keyword == 'null' || $keyword == '' ? '' : ' AND tp.fullname LIKE "%' . $keyword . '%"';
        $condition .= $knds_agree;
        //var_dump('id_campaign '.$id_campaign);
        //var_dump('id_callcode '.$id_callcode);
        //var_dump('page '.$page);
        //var_dump('value '.$pageValue);
        // not present==callback
        $id_callcode_search = $id_callcode == 12 ? 11 : $id_callcode;
        //var_dump($id_callcode_search);
        ## martin add to find child callcode
        if ($id_callcode_search != 'null' && $id_callcode_search != '' && $id_callcode_search != '0' && $id_callcode_search != '-1') {
            $this->db->where('parent_id_callcode', $id_callcode_search);
            $this->db->select('GROUP_CONCAT(id_callcode) AS childcode', FALSE);
            $qObj = $this->db->get('tb_callcode');
            if ($qObj->num_rows() > 0) {
                $qArr = $qObj->row_array();
                $childcode = $qArr['childcode'];
            } else {
                $childcode = 99;
            }
        } else if ($id_callcode_search == '0') {
            //echo 'asd';
            $childcode = '0';
        }

        //var_dump($childcode); 
        if ($id_callcode != 52)
            $condition .= $id_callcode_search == 'null' || $id_callcode_search == '' || $id_callcode_search == '-1' ? " "
                : ' AND is_agree!=1 AND tp.last_id_callcode IN(' . $childcode . ')';

        ## Recycle Flag
        if ($id_callcode_search == '-1') {
            $condition .= ' AND is_recycle = 1';
        } else if ($id_callcode_search == 'null' || $id_callcode_search == '') {
            $condition .= ' ';
        } else {
            $condition .= ' AND is_recycle <> 1';
        }

        $pageValue = $pageValue ? $pageValue : 1;
        $item_on_page = 10;

        //echo $condition . "d";
        $count = $this->prospect_model->get_count_assign($this->id_user, $condition, $keyword);

        // get list callcode
        $data['list_callcode'] = $this->callcode_model->get_list_callcode(' parent_id_callcode in (2, 49, 50, 51) ');
        //var_dump($data['list_callcode']);

        $keyword = $keyword ? $keyword : 'null';
        //$id_callcode = $id_callcode ? $id_callcode : 'null';


        //die($condition);
        // sent data
        $data['pageValue'] = $pageValue;
        $data['keyword'] = $keyword;
        //echo('kekekekek' . $keyword);

        $data['id_callcode'] = $id_callcode;
        //if(!$keyword && $id_callcode == 'null')
        //echo $keyword;
        //$condition .= $id_callcode_search == 'null' || $id_callcode_search == '' ? " and tp.last_id_callcode=0 " : ' AND is_close!=1 AND tp.last_id_callcode ="' . $id_callcode_search . '"';

        $data["page"] = makePagingPage($item_on_page, $count, $pageValue, site_url() . '/tsr/list_prospect/' . $id_campaign . '/' . $keyword . '/' . $id_callcode . '/page/', true, 'cont-prospect');
        $data["list"] = $this->prospect_model->get_list_assign($this->id_user, $condition, $keyword, $pageValue, $item_on_page);
        //echo($this->db->last_query());
        //die();
        $data["id_campaign"] = $id_campaign;
        //$data["list_header"] = $this->prospect_model->get_list_header_detail('', 1, 4, 'seq');
        $data["list_header"] = array();
        $this->load->view('tsr/list_prospect', $data);
    }

    public function list_prospect($id_campaign = "", $keyword = "null", $id_callcode = '0', $page = "", $pageValue = "")
    {
        $this->load->helper('cyrusenyx');
        //$this->output->enable_profiler(TRUE);
        //$catproduct = array('47,48,50,51,52'); // BUG tidak bisa untuk jualan xsell
        //$catproduct_str = implode(',', $catproduct);

        ## QUERY SEARCH DATA PRODUCT ACTIVE
        $sql = "SELECT GROUP_CONCAT(id_product) AS groupproduct from tb_product WHERE published=1";
        $qArr = $this->db->query($sql)->row_array();
        $catproduct_str = $qArr['groupproduct'];

        $_SESSION['last_callcode'] = $id_callcode;
        $knds_agree = ($id_callcode == 52 ? " AND tp.is_agree = 1 AND tcc.campaign_product IN ($catproduct_str)" : ' AND tp.is_agree <> 1');
        $condition = $id_campaign ? ' tp.id_campaign=' . set_string_content($id_campaign) : '';
        $condition .= $keyword == 'null' || $keyword == '' ? '' : ' AND tp.fullname LIKE "%' . $keyword . '%"';
        $condition .= $knds_agree;
        //$condition .= ' AND tp.is_agree <> 1';
        //var_dump('id_campaign '.$id_campaign);
        //var_dump('id_callcode '.$id_callcode);
        //var_dump('page '.$page);
        //var_dump('value '.$pageValue);
        // not present==callback
        $id_callcode_search = $id_callcode == 12 ? 11 : $id_callcode;
        //var_dump($id_callcode_search);
        ## martin add to find child callcode
        if ($id_callcode_search != 'null' && $id_callcode_search != '' && $id_callcode_search != '0' && $id_callcode_search != '-1') {
            $this->db->where('parent_id_callcode', $id_callcode_search);
            $this->db->select('GROUP_CONCAT(id_callcode) AS childcode', FALSE);
            $qObj = $this->db->get('tb_callcode');
            if ($qObj->num_rows() > 0) {
                $qArr = $qObj->row_array();
                $childcode = $qArr['childcode'];
            } else {
                $childcode = 99;
            }
        } else if ($id_callcode_search == '0') {
            //echo 'asd';
            $childcode = '0';
        }

        //var_dump($childcode);
        if ($id_callcode != 52)
            $condition .= $id_callcode_search == 'null' || $id_callcode_search == '' || $id_callcode_search == '-1' ? " "
                : ' AND is_agree!=1 AND tp.last_id_callcode IN(' . $childcode . ')';

        ## Recycle Flag
        if ($id_callcode_search == '-1') {
            $condition .= ' AND is_recycle = 1';
        } else if ($id_callcode_search == 'null' || $id_callcode_search == '') {
            $condition .= ' ';
        } else {
            $condition .= ' AND is_recycle <> 1';
        }

        $pageValue = $pageValue ? $pageValue : 1;
        $item_on_page = 10;

        //echo $condition . "d";
        $count = $this->prospect_model->get_count_assign($this->id_user, $condition, $keyword);

        // get list callcode
        $data['list_callcode'] = $this->callcode_model->get_list_callcode(' parent_id_callcode in (2, 49, 50, 51) ');
        //var_dump($data['list_callcode']);die();

        $keyword = $keyword ? $keyword : 'null';
        //$id_callcode = $id_callcode ? $id_callcode : 'null';


        //die($condition);
        // sent data
        $data['pageValue'] = $pageValue;
        $data['keyword'] = $keyword;
        //echo('kekekekek' . $keyword);

        $data['id_callcode'] = $id_callcode;
        //if(!$keyword && $id_callcode == 'null')
        //echo $keyword;
        //$condition .= $id_callcode_search == 'null' || $id_callcode_search == '' ? " and tp.last_id_callcode=0 " : ' AND is_close!=1 AND tp.last_id_callcode ="' . $id_callcode_search . '"';

        $data["page"] = makePagingPage($item_on_page, $count, $pageValue, site_url() . '/tsr/list_prospect/' . $id_campaign . '/' . $keyword . '/' . $id_callcode . '/page/', true, 'cont-prospect');
        //echo($this->db->last_query());
        //var_dump($data["page"]);die();
        $data["list"] = $this->prospect_model->get_list_assign($this->id_user, $condition, $keyword, $pageValue, $item_on_page);
        //echo($this->db->last_query());
        //die();
        $data["id_campaign"] = $id_campaign;
        //$data["list_header"] = $this->prospect_model->get_list_header_detail('', 1, 4, 'seq');
        $data["list_header"] = array();
        $this->load->view('tsr/list_prospect', $data);
    }

    /**
     * get individual history calltrack
     *
     * @param <type> $id_campaign
     * @param <type> $id_prospect
     * @param <type> $keyword
     * @param <type> $id_callcode
     * @param <type> $pageValue
     */
    public function history_calltrack($id_campaign, $id_prospect, $keyword, $id_callcode, $pageValue)
    {

        // sent data to view
        $data['list'] = $this->prospect_model->get_list_calltrack($id_prospect);
        $data['keyword'] = $keyword;
        $data['id_callcode'] = $id_callcode;
        $data['pageValue'] = $pageValue;
        $data['id_prospect'] = $id_prospect;
        $this->load->view('tsr/history_calltrack', $data);
    }

    //flow prospect
    public function agree($id_prospect, $id_product = '')
    {

        $data["txt_title"] = "Agree " . $id_prospect;
        $post = @$_POST['post'];

        // save detail product
        if ($post) {

            $post = array();
            foreach ($_POST as $key => $value)
                $post[$key] = $this->input->xss_clean($value);

            $arrdata = $post;
            $arrdata['id_user'] = $this->id_user;
            $arrdata['id_prospect'] = $id_prospect;
            $arrdata['created_date'] = date("Y-m-d") . ' ' . date("H:i:s");

            $this->prospect_model->add_prospect_print($arrdata);
            $this->prospect_model->add_calltrack($id_prospect, '', $this->id_user, 14, '', date("Y-m-d"), date("H:i:s"), '', '', '', '', '', '', '', '');
            $this->prospect_model->set_is_agree($id_prospect, $id_product);

            redirect("tsr/main_print/$id_prospect");
        } else {
            $data["id_product"] = $id_product;

            //get prospect
            $q = $this->db->query("select * from tb_prospect where id_prospect=$id_prospect");
            $row_prospect = $q->row_array();
            $data["row_prospect"] = $row_prospect;
        }

        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/agree', $data);
        $this->load->view('tsr/footer', $data);
    }

    function agree_v2($id_prospect, $id_product, $type = "")
    { //AGREE CC
        ## Calltrack
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_giftcode'] = $this->input->post('apl_giftcode', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_cardselect'] = $this->input->post('card_opt', TRUE);
        $post['apl_npk'] = $this->input->post('apl_npk', TRUE);

        ## Bio Data
        $post['bio_socialcode'] = $this->input->post('bio_socialcode', TRUE);
        $post['bio_socialname'] = $this->input->post('bio_socialname', TRUE);
        $post['bio_socialexp'] = $this->input->post('bio_socialexp', TRUE);
        $post['bio_socialexp'] == 'YYYY-MM-DD' ? $post['bio_socialexp'] = NULL : $post['bio_socialexp'] = $this->input->post('bio_socialexp', TRUE);

        $post['bio_identityopt'] = $this->input->post('bio_identityopt', TRUE);
        $post['bio_passportno'] = $this->input->post('bio_passportno', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['bio_embossname'] = $this->input->post('bio_embossname', TRUE);
        $post['bio_bop'] = $this->input->post('bio_bop', TRUE);
        $post['bio_dob'] = $this->input->post('bio_dob', TRUE);
        //      $post['bio_bop'] = "";
        //    	$post['bio_dob'] = "";
        $post['bio_dob'] == 'YYYY-MM-DD' ? $post['bio_dob'] = '' : $post['bio_dob'] = $this->input->post('bio_dob', TRUE);
        $post['bio_genderopt'] = $this->input->post('bio_genderopt', TRUE);
        $post['bio_nationopt'] = $this->input->post('bio_nationopt', TRUE);
        $post['bio_lasteduopt'] = $this->input->post('bio_lasteduopt', TRUE);
        $post['bio_lasteduopt'] == "OTHER" ? $post['bio_lastedu_other'] = $this->input->post('bio_lastedu_other', TRUE) : $post['bio_lastedu_other'] = NULL;

        $post['bio_sociaddr'] = $this->input->post('bio_sociaddr', TRUE);
        $post['bio_zipcode'] = $this->input->post('bio_zipcode', TRUE);
        $post['bio_kelurahan'] = $this->input->post('bio_kelurahan', TRUE);
        $post['bio_kecamatan'] = $this->input->post('bio_kecamatan', TRUE);
        $post['bio_rt'] = $this->input->post('bio_rt', TRUE);
        $post['bio_rw'] = $this->input->post('bio_rw', TRUE);
        $post['bio_city'] = $this->input->post('bio_city', TRUE);
        $post['bio_kabupaten'] = $this->input->post('bio_kabupaten', TRUE);
        $post['bio_maritalopt'] = $this->input->post('bio_maritalopt', TRUE);
        $post['bio_dependent'] = $this->input->post('bio_dependent', TRUE);
        $post['bio_npwp'] = $this->input->post('bio_npwp', TRUE);
        $post['bio_email'] = $this->input->post('bio_email', TRUE);
        $post['bio_maidenname'] = $this->input->post('bio_maidenname', TRUE);
        //$post['bio_maidenname'] = "";
        $post['bio_religion'] = $this->input->post('bio_religion', TRUE);
        $post['bio_billaddr'] = $this->input->post('bio_billaddr', TRUE);
        $post['bio_billzipcode'] = $this->input->post('bio_billzipcode', TRUE);
        $post['bio_billkelurahan'] = $this->input->post('bio_billkelurahan', TRUE);
        $post['bio_billkecamatan'] = $this->input->post('bio_billkecamatan', TRUE);
        $post['bio_religion'] = $this->input->post('bio_religion', TRUE);
        $post['bio_billrt'] = $this->input->post('bio_billrt', TRUE);
        $post['bio_billrw'] = $this->input->post('bio_billrw', TRUE);
        $post['bio_billcity'] = $this->input->post('bio_billcity', TRUE);
        $post['bio_billkabupaten'] = $this->input->post('bio_billkabupaten', TRUE);
        $post['bio_billresideyear'] = $this->input->post('bio_billresideyear', TRUE);
        $post['bio_billresidemonth'] = $this->input->post('bio_billresidemonth', TRUE);
        $post['bio_billhomephonearea'] = $this->input->post('bio_billhomephonearea', TRUE);
        $post['bio_billhomephone'] = $this->input->post('bio_billhomephone', TRUE);
        $post['bio_billcellular'] = $this->input->post('bio_billcellular', TRUE);
        $post['billhome_opt'] = $this->input->post('billhome_opt', TRUE);
        $post['bio_mailingopt'] = $this->input->post('bio_mailingopt', TRUE);

        ## Data Pekerjaan
        $post['ocp_occupationopt'] = $this->input->post('ocp_occupationopt', TRUE);
        $post['ocp_occustatopt'] = $this->input->post('ocp_occustatopt', TRUE);
        $post['ocp_occupationopt'] == "OTHER" ? $post['ocp_occupation_other'] = $this->input->post('ocp_occupation_other', TRUE) : $post['ocp_occupation_other'] = NULL;
        $post['ocp_occutypeopt'] = $this->input->post('ocp_occutypeopt', TRUE);
        $post['ocp_occutypeopt'] == "OTHER" ? $post['ocp_occutype_other'] = $this->input->post('ocp_occutype_other', TRUE) : $post['ocp_occutype_other'] = NULL;
        $post['have_slipgaji'] = $this->input->post('have_slipgaji', TRUE) == 'on' ? $post['have_slipgaji'] = 1 : $post['have_slipgaji'] = 0;
        $post['ocp_businessline'] = $this->input->post('ocp_businessline', TRUE);
        $post['ocp_companyname'] = $this->input->post('ocp_companyname', TRUE);
        $post['ocp_officeaddr'] = $this->input->post('ocp_officeaddr', TRUE);
        $post['ocp_kelurahan'] = $this->input->post('ocp_kelurahan', TRUE);
        $post['ocp_kecamatan'] = $this->input->post('ocp_kecamatan', TRUE);
        $post['ocp_rt'] = $this->input->post('ocp_rt', TRUE);
        $post['ocp_rw'] = $this->input->post('ocp_rw', TRUE);
        $post['ocp_zipcode'] = $this->input->post('ocp_zipcode', TRUE);
        $post['ocp_city'] = $this->input->post('ocp_city', TRUE);
        $post['ocp_kabupaten'] = $this->input->post('ocp_kabupaten', TRUE);
        $post['ocp_officephonearea'] = $this->input->post('ocp_officephonearea', TRUE);
        $post['ocp_officephone'] = $this->input->post('ocp_officephone', TRUE);
        $post['ocp_officeext'] = $this->input->post('ocp_officeext', TRUE);
        $post['ocp_fax'] = $this->input->post('ocp_fax', TRUE);
        $post['ocp_empcount'] = $this->input->post('ocp_empcount', TRUE);
        $post['ocp_division'] = $this->input->post('ocp_division', TRUE);
        $post['ocp_department'] = $this->input->post('ocp_department', TRUE);
        $post['ocp_position'] = $this->input->post('ocp_position', TRUE);
        $post['ocp_yearduration'] = $this->input->post('ocp_yearduration', TRUE);
        $post['ocp_monthduration'] = $this->input->post('ocp_monthduration', TRUE);
        $post['ocp_lastyearduration'] = $this->input->post('ocp_lastyearduration', TRUE);
        $post['ocp_lastmonthduration'] = $this->input->post('ocp_lastmonthduration', TRUE);
        $post['ocp_grossincome'] = $this->input->post('ocp_grossincome', TRUE);
        $post['ocp_additionalincome'] = $this->input->post('ocp_additionalincome', TRUE);
        $post['ocp_additionalsource'] = $this->input->post('ocp_additionalsource', TRUE);
        $post['ocp_cc'] = $this->input->post('ocp_cc', TRUE);

        ## Referensi bank
        $post['ref_bankname'] = $this->input->post('ref_bankname', TRUE);
        $post['ref_bankname_2'] = $this->input->post('ref_bankname_2', TRUE);
        $post['ref_bankname_3'] = $this->input->post('ref_bankname_3', TRUE);
        $post['ref_existingcard'] = $this->input->post('ref_existingcard', TRUE);
        $post['ref_existingcard_2'] = $this->input->post('ref_existingcard_2', TRUE);
        $post['ref_existingcard_3'] = $this->input->post('ref_existingcard_3', TRUE);
        $post['ref_accountno'] = $this->input->post('ref_accountno', TRUE);
        $post['ref_accountno_2'] = $this->input->post('ref_accountno_2', TRUE);
        $post['ref_accountno_3'] = $this->input->post('ref_accountno_3', TRUE);

        ## Supplement card 1
        $post['sup_fullname'] = $this->input->post('sup_fullname', TRUE);
        $post['sup_embossname'] = $this->input->post('sup_embossname', TRUE);
        $post['sup_socialcode'] = $this->input->post('sup_socialcode', TRUE);
        $post['sup_identityopt'] = $this->input->post('sup_identityopt', TRUE);
        $post['sup_genderopt'] = $this->input->post('sup_genderopt', TRUE);
        $post['sup_dob'] = $this->input->post('sup_dob', TRUE) == 'YYYY-MM-DD' ? $post['sup_dob'] = NULL : $post['sup_dob'] = $this->input->post('sup_dob', TRUE);
        $post['sup_pob'] = $this->input->post('sup_pob', TRUE);
        $post['sup_maidenname'] = $this->input->post('sup_maidenname', TRUE);
        $post['sup_cardlimit'] = $this->input->post('sup_cardlimit', TRUE);
        $post['sup_relationopt'] = $this->input->post('sup_relationopt', TRUE);

        ## Supplement card 2
        $post['sup_fullname_2'] = $this->input->post('sup_fullname_2', TRUE);
        $post['sup_embossname_2'] = $this->input->post('sup_embossname_2', TRUE);
        $post['sup_socialcode_2'] = $this->input->post('sup_socialcode_2', TRUE);
        $post['sup_identityopt_2'] = $this->input->post('sup_identityopt_2', TRUE);
        $post['sup_genderopt_2'] = $this->input->post('sup_genderopt_2', TRUE);
        $post['sup_dob_2'] = $this->input->post('sup_dob_2', TRUE) == 'YYYY-MM-DD' ? $post['sup_dob_2'] = NULL : $post['sup_dob_2'] = $this->input->post('sup_dob_2', TRUE);
        $post['sup_pob_2'] = $this->input->post('sup_pob_2', TRUE);
        $post['sup_maidenname_2'] = $this->input->post('sup_maidenname_2', TRUE);
        $post['sup_cardlimit_2'] = $this->input->post('sup_cardlimit_2', TRUE);
        $post['sup_relationopt_2'] = $this->input->post('sup_relationopt_2', TRUE);

        ## Supplement card 3
        $post['sup_fullname_3'] = $this->input->post('sup_fullname_3', TRUE);
        $post['sup_embossname_3'] = $this->input->post('sup_embossname_3', TRUE);
        $post['sup_socialcode_3'] = $this->input->post('sup_socialcode_3', TRUE);
        $post['sup_identityopt_3'] = $this->input->post('sup_identityopt_3', TRUE);
        $post['sup_genderopt_3'] = $this->input->post('sup_genderopt_3', TRUE);
        $post['sup_dob_3'] = $this->input->post('sup_dob_3', TRUE) == 'YYYY-MM-DD' ? $post['sup_dob_3'] = NULL : $post['sup_dob_3'] = $this->input->post('sup_dob_3', TRUE);
        $post['sup_pob_3'] = $this->input->post('sup_pob_3', TRUE);
        $post['sup_maidenname_3'] = $this->input->post('sup_maidenname_3', TRUE);
        $post['sup_cardlimit_3'] = $this->input->post('sup_cardlimit_3', TRUE);
        $post['sup_relationopt_3'] = $this->input->post('sup_relationopt_3', TRUE);

        ## Supplement card 4
        $post['sup_fullname_4'] = $this->input->post('sup_fullname_4', TRUE);
        $post['sup_embossname_4'] = $this->input->post('sup_embossname_4', TRUE);
        $post['sup_socialcode_4'] = $this->input->post('sup_socialcode_4', TRUE);
        $post['sup_identityopt_4'] = $this->input->post('sup_identityopt_4', TRUE);
        $post['sup_genderopt_4'] = $this->input->post('sup_genderopt_4', TRUE);
        $post['sup_dob_4'] = $this->input->post('sup_dob_4', TRUE) == 'YYYY-MM-DD' ? $post['sup_dob_4'] = NULL : $post['sup_dob_4'] = $this->input->post('sup_dob_4', TRUE);
        $post['sup_pob_4'] = $this->input->post('sup_pob_4', TRUE);
        $post['sup_maidenname_4'] = $this->input->post('sup_maidenname_4', TRUE);
        $post['sup_cardlimit_4'] = $this->input->post('sup_cardlimit_4', TRUE);
        $post['sup_relationopt_4'] = $this->input->post('sup_relationopt_4', TRUE);

        ## Emergency Contact
        $post['emr_emergencyname'] = $this->input->post('emr_emergencyname', TRUE);
        $post['emr_emergencyaddr'] = $this->input->post('emr_emergencyaddr', TRUE);
        $post['emr_kelurahan'] = $this->input->post('emr_kelurahan', TRUE);
        $post['emr_kecamatan'] = $this->input->post('emr_kecamatan', TRUE);
        $post['emr_rt'] = $this->input->post('emr_rt', TRUE);
        $post['emr_rw'] = $this->input->post('emr_rw', TRUE);
        $post['emr_zipcode'] = $this->input->post('emr_zipcode', TRUE);
        $post['emr_city'] = $this->input->post('emr_city', TRUE);
        $post['emr_kabupaten'] = $this->input->post('emr_kabupaten', TRUE);
        $post['emr_phoneareacode'] = $this->input->post('emr_phoneareacode', TRUE);
        $post['emr_homephone'] = $this->input->post('emr_homephone', TRUE);
        $post['emr_cellular'] = $this->input->post('emr_cellular', TRUE);
        $post['emr_relationopt'] = $this->input->post('emr_relationopt', TRUE);
        $post['emr_relationopt'] == 'OTHER' ? $post['emr_relationother'] = $this->input->post('emr_relationother', TRUE) : $post['emr_relationother'] = NULL;

        ## Surat Kuasa
        $post['sku_accountname'] = $this->input->post('sku_accountname', TRUE);
        $post['sku_accountnumber'] = $this->input->post('sku_accountnumber', TRUE);
        $post['sku_paytype'] = $this->input->post('sku_paytype', TRUE);

        ## Informasi Penyandang Dana
        $post['fun_fullname'] = $this->input->post('fun_fullname', TRUE);
        $post['fun_socialcode'] = $this->input->post('fun_socialcode', TRUE);
        $post['fun_relation'] = $this->input->post('fun_relation', TRUE);
        $post['fun_npwp'] = $this->input->post('fun_npwp', TRUE);
        $post['fun_occupation'] = $this->input->post('fun_occupation', TRUE);
        $post['fun_businessline'] = $this->input->post('fun_businessline', TRUE);
        $post['fun_yearincome'] = $this->input->post('fun_yearincome', TRUE);
        $post['fun_rek'] = $this->input->post('fun_rek', TRUE);

        ## Data Pickup
        $post['pickup_opt'] = $this->input->post('pickup_opt', TRUE);
        $post['pku_addr'] = $this->input->post('pku_addr', TRUE);
        $post['pku_zipcode'] = $this->input->post('pku_zipcode', TRUE);
        $post['pku_kelurahan'] = $this->input->post('pku_kelurahan', TRUE);
        $post['pku_kecamatan'] = $this->input->post('pku_kecamatan', TRUE);
        $post['pku_rt'] = $this->input->post('pku_rt', TRUE);
        $post['pku_rw'] = $this->input->post('pku_rw', TRUE);
        $post['pku_city'] = $this->input->post('pku_city', TRUE);
        $post['pku_kabupaten'] = $this->input->post('pku_kabupaten', TRUE);
        $post['pku_date'] = $this->input->post('pku_date', TRUE);
        $post['pku_date'] == '' ? $post['pku_date'] = NULL : $post['pku_date'] = $this->input->post('pku_date', TRUE);
        $post['pku_notes'] = $this->input->post('pku_notes', TRUE);
        $post['pku_zone'] = $this->input->post('zone_id', TRUE);
        //$post['kurir_id'] = $this->assign_courier($post['pku_zone'], $post['pku_date']);
        $post['pku_courier'] = $this->assign_courier($post['pku_zone'], $post['pku_date']);

        ## Data Tambahan / Misc
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);
        $post['program_1'] = $this->input->post('program_1', TRUE);
        $post['program_2'] = $this->input->post('program_2', TRUE);
        $post['program_3'] = $this->input->post('program_3', TRUE);

        ## Data Campaign dan product 
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'CC';

        //var_dump($post['pku_courier']); die();

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();

        ## Check Campaign Type    
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV when camptype is set to must listen
        if ($campaign_type == '1' || $campaign_type == '6' || $campaign_type == '7') {
            $update = array(
                'spv_holdstatus' => '1013',
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1013',
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1013', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'SPV'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Default ke sukses pickup kalau ada janji pickup oleh TSR
        if ($post['pku_date'] != NULL) {
            $update_print['appointment_status'] = '1001';
            $update_print['appointment_date'] = DATE('Y-m-d H:i:s');
            $update_print['appointed_by'] = $_SESSION['id_user'];
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update_print);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'date_pickup' => $post['pku_date'],
                'courier_code' => $post['pku_courier'],
                'pickup_city' => $post['pku_city'],
                'status_code' => '1001', //STATUSnya Success Appoinment
                'pku_notes' => $post['pku_notes'],
                'agree_notes' => $post['agree_notes'],
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'FU'
            );
            $this->db->insert('tb_statustrack', $statustrack);
            $st_insert_id = $this->db->insert_id();
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data for QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s'),
            'emergency_homephone' => $post['emr_phoneareacode'] . $post['emr_homephone'],
            'emergency_cellular' => $post['emr_cellular']
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        $this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        $this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        if ($type != 'BUNDLING') {
            redirect('tsr/main');
            exit;
        } else {
            echo $insert_id;
            die();
        }
    }

    function agree_v3($id_prospect, $id_product, $type = "")
    { //AGREE PL

        ## calltrack
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        //$post['apl_seqno'] = $this->input->post('apl_seqno' ,TRUE);
        $post['apl_giftcode'] = $this->input->post('apl_giftcode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);

        ## Bio Data
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['bio_titlebefore'] = $this->input->post('bio_titlebefore', TRUE); ##Umrah
        $post['bio_titleafter'] = $this->input->post('bio_titleafter', TRUE); ##Umrah
        $post['bio_socialname'] = $this->input->post('bio_socialname', TRUE);
        $post['bio_socialcode'] = $this->input->post('bio_socialcode', TRUE);
        $post['bio_inisial'] = $this->input->post('bio_inisial', TRUE);
        $post['bio_socialexp'] = $this->input->post('bio_socialexp', TRUE);
        $post['bio_socialexp'] == 'YYYY-MM-DD' ? $post['bio_socialexp'] = NULL : $post['bio_socialexp'] = $this->input->post('bio_socialexp', TRUE);
        $post['bio_bop'] = $this->input->post('bio_bop', TRUE);
        //$post['bio_dob'] = $this->input->post('bio_dob' ,TRUE);
        //$post['bio_dob'] == 'YYYY-MM-DD' ? $post['bio_dob'] = NULL : $post['bio_dob'] = $this->input->post('bio_dob' ,TRUE);
        $post['bio_dob'] = "";
        $post['bio_genderopt'] = $this->input->post('bio_genderopt', TRUE);
        $post['bio_sociaddr'] = $this->input->post('bio_sociaddr', TRUE);
        $post['bio_rt'] = $this->input->post('bio_rt', TRUE);
        $post['bio_rw'] = $this->input->post('bio_rw', TRUE);
        $post['bio_cif'] = $this->input->post('bio_cif', TRUE);
        $post['bio_kelurahan'] = $this->input->post('bio_kelurahan', TRUE);
        $post['bio_kecamatan'] = $this->input->post('bio_kecamatan', TRUE);
        $post['bio_kabupaten'] = $this->input->post('bio_kabupaten', TRUE);
        $post['bio_city'] = $this->input->post('bio_city', TRUE);
        $post['bio_zipcode'] = $this->input->post('bio_zipcode', TRUE);
        $post['bio_addr'] = $this->input->post('bio_addr', TRUE);
        $post['bio_rt'] = $this->input->post('bio_rt', TRUE);
        $post['bio_rw'] = $this->input->post('bio_rw', TRUE);
        $post['bio_billrt'] = $this->input->post('bio_billrt', TRUE);
        $post['bio_billrw'] = $this->input->post('bio_billrw', TRUE);
        $post['bio_billkelurahan'] = $this->input->post('bio_billkelurahan', TRUE);
        $post['bio_billkecamatan'] = $this->input->post('bio_billkecamatan', TRUE);
        $post['bio_billkabupaten'] = $this->input->post('bio_billkabupaten', TRUE);
        $post['bio_billcity'] = $this->input->post('bio_billcity', TRUE);
        $post['bio_billzipcode'] = $this->input->post('bio_billzipcode', TRUE);
        $post['bio_lasteduopt'] = $this->input->post('bio_lasteduopt', TRUE);
        $post['bio_lasteduopt'] == 'OTHER' ? $post['bio_lastedu_other'] = $this->input->post('bio_lastedu_other', TRUE) : $post['bio_lastedu_other'] = NULL;
        $post['bio_billresideyear'] = $this->input->post('bio_billresideyear', TRUE);
        $post['bio_billresidemonth'] = $this->input->post('bio_billresidemonth', TRUE);
        $post['bio_billhomephonearea'] = $this->input->post('bio_billhomephonearea', TRUE);
        $post['bio_billhomephone'] = $this->input->post('bio_billhomephone', TRUE);
        $post['billhome_opt'] = $this->input->post('billhome_opt', TRUE);
        $post['bio_billcellular'] = $this->input->post('bio_billcellular', TRUE);
        $post['bio_npwp'] = $this->input->post('bio_npwp', TRUE);
        $post['bio_npwp_opt'] = $this->input->post('bio_npwp_opt', TRUE);
        $post['bio_email'] = $this->input->post('bio_email', TRUE);
        $post['bio_religion'] = $this->input->post('bio_religion', TRUE);
        $post['bio_maidenname'] = $this->input->post('bio_maidenname', TRUE);
        //$post['bio_maidenname'] = "";
        $post['bio_maritalopt'] = $this->input->post('bio_maritalopt', TRUE);
        $post['bio_dependent'] = $this->input->post('bio_dependent', TRUE);
        $post['bio_mailingopt'] = $this->input->post('bio_mailingopt', TRUE);
        $post['bio_spouse'] = $this->input->post('bio_spouse', TRUE);


        ## Data Pekerjaan
        $post['ocp_occupationopt'] = $this->input->post('ocp_occupationopt', TRUE);
        $post['ocp_occutypeopt'] = $this->input->post('ocp_occutypeopt', TRUE);
        $post['ocp_companyname'] = $this->input->post('ocp_companyname', TRUE); //== 'on' ? $post['have_slipgaji'] = 1 : $post['have_slipgaji'] = 0;
        $post['ocp_businessline'] = $this->input->post('ocp_businessline', TRUE);
        $post['ocp_officeaddr'] = $this->input->post('ocp_officeaddr', TRUE);
        $post['ocp_yearduration'] = $this->input->post('ocp_yearduration', TRUE);
        $post['ocp_monthduration'] = $this->input->post('ocp_monthduration', TRUE);
        $post['ocp_lastyearduration'] = $this->input->post('ocp_lastyearduration', TRUE);
        $post['ocp_lastmonthduration'] = $this->input->post('ocp_lastmonthduration', TRUE);
        $post['ocp_empcount'] = $this->input->post('ocp_empcount', TRUE);
        $post['ocp_occustatopt'] = $this->input->post('ocp_occustatopt', TRUE);
        $post['ocp_position'] = $this->input->post('ocp_position', TRUE);
        $post['ocp_division'] = $this->input->post('ocp_division', TRUE);
        $post['ocp_kelurahan'] = $this->input->post('ocp_kelurahan', TRUE);
        $post['ocp_kecamatan'] = $this->input->post('ocp_kecamatan', TRUE);
        $post['ocp_kabupaten'] = $this->input->post('ocp_kabupaten', TRUE);
        $post['ocp_city'] = $this->input->post('ocp_city', TRUE);
        $post['ocp_zipcode'] = $this->input->post('ocp_zipcode', TRUE);
        $post['ocp_officephonearea'] = $this->input->post('ocp_officephonearea', TRUE);
        $post['ocp_officephone'] = $this->input->post('ocp_officephone', TRUE);
        $post['ocp_officeext'] = $this->input->post('ocp_officeext', TRUE);
        $post['ocp_fax'] = $this->input->post('ocp_fax', TRUE);
        $post['ocp_grossincome'] = $this->input->post('ocp_grossincome', TRUE);
        $post['ocp_additionalincome'] = $this->input->post('ocp_additionalincome', TRUE);
        $post['ocp_additionalsource'] = $this->input->post('ocp_additionalsource', TRUE);
        $post['ocp_nosiup'] = $this->input->post('ocp_nosiup', TRUE);
        $post['ocp_omset'] = $this->input->post('ocp_omset', TRUE);
        $post['ocp_persen'] = $this->input->post('ocp_persen', TRUE);
        $post['ocp_tgl'] = $this->input->post('ocp_tgl', TRUE);
        $post['ocp_bln'] = $this->input->post('ocp_bln', TRUE);
        $post['ocp_thn'] = $this->input->post('ocp_thn', TRUE);

        ## Data Keluarga
        $post['fam_fullname'] = $this->input->post('fam_fullname', TRUE);
        $post['fam_addr'] = $this->input->post('fam_addr', TRUE);
        $post['fam_rt'] = $this->input->post('fam_rt', TRUE);
        $post['fam_rw'] = $this->input->post('fam_rw', TRUE);
        $post['fam_kelurahan'] = $this->input->post('fam_kelurahan', TRUE);
        $post['fam_kecamatan'] = $this->input->post('fam_kecamatan', TRUE);
        $post['fam_kabupaten'] = $this->input->post('fam_kabupaten', TRUE);
        $post['fam_city'] = $this->input->post('fam_city', TRUE);
        $post['fam_zipcode'] = $this->input->post('fam_zipcode', TRUE);
        $post['fam_homephonearea'] = $this->input->post('fam_homephonearea', TRUE);
        $post['fam_homephone'] = $this->input->post('fam_homephone', TRUE);
        $post['fam_cellular'] = $this->input->post('fam_cellular', TRUE);
        $post['fam_relationopt'] = $this->input->post('fam_relationopt', TRUE);
        $post['fam_relationopt'] == 'OTHER' ? $post['fam_relationoth'] = $this->input->post('fam_relationoth', TRUE) : $post['fam_relationoth'] = NULL;
        $post['fam_officephonearea'] = $this->input->post('fam_officephonearea', TRUE);
        $post['fam_officephone'] = $this->input->post('fam_officephone', TRUE);
        $post['famhome_opt'] = $this->input->post('famhome_opt', TRUE);
        $post['fam_billresideyear'] = $this->input->post('fam_billresideyear', TRUE);
        $post['fam_billresidemonth'] = $this->input->post('fam_billresidemonth', TRUE);

        ## Fasilitas Pinjaman
        $post['ben_limiteditionopt'] = $this->input->post('ben_limiteditionopt', TRUE);
        $post['ben_pinjamopt'] = $this->input->post('ben_pinjamopt', TRUE);
        $post['ben_pinjamopt'] == 'LAINNYA' ? $post['ben_other'] = $this->input->post('ben_other', TRUE) : $post['ben_other'] = NULL;
        $post['ben_pinjamincome'] = price_format($this->input->post('ben_pinjamincome', TRUE));
        $post['ben_cicilanincome'] = $this->input->post('ben_cicilanincome', TRUE);
        $post['ben_bunga'] = $this->input->post('ben_bunga', TRUE); // == 'YYYY-MM-DD' ? $post['sup_dob'] = NULL : $post['sup_dob'] = $this->input->post('sup_dob' ,TRUE);
        $post['ben_bunga_ef'] = $this->input->post('ben_bunga_ef', TRUE);
        $post['ben_pinjamopt'] == 'LAINNYAJASA' ? $post['ben_otherjasa'] = $this->input->post('ben_otherjasa', TRUE) : $post['ben_otherjasa'] = NULL;
        $post['ben_materai'] = $this->input->post('ben_materai', TRUE);

        ## Benefit Khusus Umrah
        $post['ben_depatureperson'] = $this->input->post('ben_depatureperson', TRUE) != ""  ? $post['ben_depatureperson'] = $this->input->post('ben_depatureperson', TRUE) : $post['ben_depatureperson'] = NULL;
        $post['ben_depaturedate'] = $this->input->post('ben_depaturedate', TRUE) != "" ? $post['ben_depaturedate'] = $this->input->post('ben_depaturedate', TRUE) : $post['ben_depaturedate'] = NULL;
        $post['ben_travelagent'] = $this->input->post('ben_travelagent', TRUE) != "" ? $post['ben_travelagent'] = $this->input->post('ben_travelagent', TRUE) : $post['ben_travelagent'] = NULL;
        //$post['ben_travelcabang'] = $this->input->post('ben_travelcabang' ,TRUE) != "" ? $post['ben_travelcabang'] = $this->input->post('ben_travelcabang' ,TRUE) : $post['ben_travelcabang'] = NULL;

        ## Data Pinjaman
        $post['pjm_fullname'] = $this->input->post('pjm_fullname', TRUE);
        $post['pjm_genderopt'] = $this->input->post('pjm_genderopt', TRUE);
        $post['pjm_other'] = $this->input->post('pjm_other', TRUE);
        $post['pjm_year'] = $this->input->post('pjm_year', TRUE);
        $post['pjm_resideyear'] = $this->input->post('pjm_resideyear', TRUE);
        $post['pjm_residemonth'] = $this->input->post('pjm_residemonth', TRUE);
        $post['pjm_pinjcount'] = $this->input->post('pjm_pinjcount', TRUE);
        $post['pjm_angsuran'] = $this->input->post('pjm_angsuran', TRUE);
        $post['pjm_nokartu'] = $this->input->post('pjm_nokartu', TRUE);

        $post['pjm_bankopt'] = $this->input->post('pjm_bankopt', TRUE);
        $post['pjm_accnopermata'] = $this->input->post('pjm_accnopermata', TRUE);
        $post['pjm_accnoother'] = $this->input->post('pjm_accnoother', TRUE);
        $post['pjm_banknameother'] = $this->input->post('pjm_banknameother', TRUE);
        $post['pjm_accname'] = $this->input->post('pjm_accname', TRUE);
        $post['pjm_name'] = $this->input->post('pjm_name', TRUE);
        $post['pjm_bankname'] = $this->input->post('pjm_bankname', TRUE);
        $post['pjm_accbranch'] = $this->input->post('pjm_accbranch', TRUE);
        $post['pjm_norek'] = $this->input->post('pjm_norek', TRUE);
        $post['pjm_cabang'] = $this->input->post('pjm_cabang', TRUE);
        $post['pjm_namerek'] = $this->input->post('pjm_namerek', TRUE);
        $post['pjm_telarea'] = $this->input->post('pjm_telarea', TRUE);
        $post['pjm_telpon'] = $this->input->post('pjm_telpon', TRUE);

        ## Pembayaran Angsuran
        $post['ang_autodebet'] = $this->input->post('ang_autodebet', TRUE) == '1' ? $post['ang_autodebet'] = 1 : $post['ang_autodebet'] = 0;
        $post['ang_rekening'] = $post['ang_autodebet'] == 1 ? $post['ang_rekening'] = $this->input->post('ang_rekening', TRUE) : $post['ang_rekening'] = NULL;
        $post['ang_cif'] = $post['ang_autodebet'] == 1 ? $post['ang_cif'] = $this->input->post('ang_cif', TRUE) : $post['ang_cif'] = NULL;
        $post['ang_accname'] = $post['ang_autodebet'] == 1 ? $post['ang_accname'] = $this->input->post('ang_accname', TRUE) : $post['ang_accname'] = NULL;
        $post['ang_cabang'] = $post['ang_autodebet'] == 1 ? $post['ang_cabang'] = $this->input->post('ang_cabang', TRUE) : $post['ang_cabang'] = NULL;
        $post['ang_paytype'] = $post['ang_autodebet'] == 1 ? $post['ang_paytype'] = $this->input->post('ang_paytype', TRUE) : $post['ang_paytype'] = NULL;

        ## Data pickup
        $post['pickup_opt'] = $this->input->post('pickup_opt', TRUE);
        $post['pku_addr'] = $this->input->post('pku_addr', TRUE);
        $post['pku_zipcode'] = $this->input->post('pku_zipcode', TRUE);
        $post['pku_kelurahan'] = $this->input->post('pku_kelurahan', TRUE);
        $post['pku_kecamatan'] = $this->input->post('pku_kecamatan', TRUE);
        $post['pku_kabupaten'] = $this->input->post('pku_kabupaten', TRUE);
        $post['pku_rt'] = $this->input->post('pku_rt', TRUE);
        $post['pku_rw'] = $this->input->post('pku_rw', TRUE);
        $post['pku_city'] = $this->input->post('pku_city', TRUE);
        $post['pku_date'] = $this->input->post('pku_date', TRUE);
        $post['pku_date'] == '' ? $post['pku_date'] = NULL : $post['pku_date'] = $this->input->post('pku_date', TRUE);
        $post['pku_notes'] = $this->input->post('pku_notes', TRUE);
        $post['pku_memo'] = $this->input->post('pku_memo', TRUE);
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);
        $post['pku_zone'] = $this->input->post('zone_id', TRUE);
        //$post['kurir_id'] = $this->assign_courier($post['pku_zone'], $post['pku_date']);
        $post['pku_courier'] = $this->assign_courier($post['pku_zone'], $post['pku_date']);
        $post['tgl_cycle'] = '0000-00-00';

        ## Program
        $post['program_1'] = $this->input->post('program_1', TRUE);
        $post['program_2'] = $this->input->post('program_2', TRUE);
        $post['program_3'] = $this->input->post('program_3', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'PL';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);
        $emr_hold = 0; // initial value

        //var_dump($post); die();

        ## Nentuin campaign type buat cross sale multiguna
        $this->load->model('misc/misc_model');

        if (!$buy_camptype) {
            $camp_type = $this->get_camptype($post['id_campaign']);

            if ($camp_type == '2' || $camp_type == '3') {
                if ($post['fam_addr'] == NULL || $post['fam_addr'] == "") {
                    $emr_hold = 1;
                }
            }
            $post['buy_camptype'] = $this->misc_model->get_camptypecode($camp_type);
        } else {
            $post['buy_camptype'] = $buy_camptype;
        }
        //var_dump($post); die();

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();

        ## Check Campaign Type    
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1015',
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1015',
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1015', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'SPV'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => $emr_hold,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        // $calltrackData = array(
        //  'id_callcode'=> 52, //52 == agree
        //  'remark'=> $post['agree_notes'],
        //  'id_product'=> $id_product
        // );
        // $this->db->where('id_calltrack', $id_calltrack);
        // $this->db->update('tb_calltrack', $calltrackData);

        // SCRIPT BARU
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product,
            'outcall_id' => $this->input->post('outcall_id_submit'),
            'caller_id' => $this->input->post('caller_id_submit'),
            'outcall_start' => $this->input->post('outcall_start_submit'),
            'outcall_duration' => $this->input->post('outcall_sec_realtime_submit'),
            'rec_id' => $this->input->post('rec_id_submit'),
            'rec_filename' => $this->input->post('rec_filename_submit'),
            'end_call_time' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update dispo
        $this->load->model('curl_model');
        $this->load->model('misc/misc_model');
        $dispo = $this->misc_model->get_tableDataById('tb_callcode', 52, 'id_callcode', 'vcode');
        $dispo_nextpause = $this->input->post('dispo_nextpause');

        $targeturl = "http://" . ip_server() . "/vtel/index.php/api/dispo/submitDispo";
        $postdata = array(
            'session_name' => $_SESSION['session_name'],
            'outcall_id' => $this->input->post('outcall_id_submit'),
            'dispo_choice' => $dispo,
            'dispo_nextpause' => $dispo_nextpause == 'on' ? 'true' : 'false',
            'callback_dt' => ''
        );
        $curlResp = $this->curl_model->curlpost($targeturl, $postdata);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s'),
            'emergency_homephone' => $post['fam_homephonearea'] . $post['fam_homephone'],
            'emergency_cellular' => $post['fam_cellular']
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        //$this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        //$this->check_holdedrandom($_SESSION['id_user'], $id_prospect);
        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }


    function agree_cp($id_prospect, $id_product, $type = "")
    { // AGREE CP
        ## calltrack
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);
        $cardlist = $this->input->post('cardnumber_list', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'CP';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();

        //var_dump($cardlist); die();
        if ($insert_id > 0) {
            foreach ($cardlist as $card) {
                $insert = array(
                    'print_idx' => $insert_id,
                    'id_prospect' => $id_prospect,
                    'cardnumber' => $card
                );
                $this->db->insert('tb_creditprotection', $insert);
            }
        }

        ## Check Campaign Type    
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1013',
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1013',
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1013', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'SPV'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => 0,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        $this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        $this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }

    function agree_bp($id_prospect, $id_product, $type = "")
    { // AGREE BP
        ## calltrack
        $id_calltrack = $this->input->post('id_calltrack', TRUE);
        $uniqid =  $this->input->post('uniqid');

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'BP';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();

        ## Get Bill Payment List
        $this->load->model('billpayment_model');
        $bills = $this->billpayment_model->get_tempbills($uniqid);
        if ($insert_id > 0) {
            foreach ($bills as $bill) :
                $insert = array(
                    'print_idx' => $insert_id,
                    'cardnumber' => $bill['cardnumber'],
                    'provider_id' => $bill['provider_id'],
                    'providerchild_id' => $bill['providerchild_id'],
                    'areacode' => $bill['areacode'],
                    'bill_number' => $bill['bill_number'],
                    'bill_amount' => $bill['bill_amount'],
                    'enroll_name' => $bill['enroll_name'],
                    'effective_date' => $bill['effective_date'],
                    'description' => $bill['description']
                );
                $this->db->insert('tb_billpayment', $insert);
            endforeach;
        }

        ## Check Campaign Type    
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1013',
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1013',
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1013', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'SPV'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => 0,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        $this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        $this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }

    function agree_pj($id_prospect, $id_product, $type = "")
    { // AGREE PJ
        ## calltrack
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'PJ';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();

        ## Insert Data Tertanggung
        $cardnumber = $this->input->post('cardnumber', TRUE);
        $max_loop = 10;

        for ($i = 1; $i <= $max_loop; $i++) {
            $tg_policy_no = $this->input->post('tg_policy_no-' . $i, TRUE);
            if (!empty($tg_policy_no) && $insert_id > 0) {
                ## Data Tertanggung
                $tertanggung['print_idx'] = $insert_id;
                $tertanggung['prospect_id'] = $this->input->post('apl_appid', TRUE);
                $tertanggung['cardnumber'] = $cardnumber;
                $tertanggung['tg_policy_no'] = $this->input->post('tg_policy_no-' . $i, TRUE);
                $tertanggung['tg_fullname'] = $this->input->post('tg_fullname-' . $i, TRUE);
                $tertanggung['tg_social_number'] = $this->input->post('tg_social_number-' . $i, TRUE);
                $tertanggung['tg_email'] = $this->input->post('tg_email-' . $i, TRUE);
                $tertanggung['tg_dob'] = $this->input->post('tg_dob-' . $i, TRUE);
                $tertanggung['tg_pob'] = $this->input->post('tg_pob-' . $i, TRUE);
                $tertanggung['tg_umur'] = $this->input->post('tg_umur-' . $i, TRUE);
                $tertanggung['tg_gender'] = $this->input->post('tg_gender-' . $i, TRUE);
                $tertanggung['tg_ch_relation'] = $this->input->post('tg_ch_relation-' . $i, TRUE);
                $tertanggung['tg_marital_status'] = $this->input->post('tg_marital_status-' . $i, TRUE);
                $tertanggung['tg_plan'] = $this->input->post('tg_plan-' . $i, TRUE);
                $tertanggung['tg_premium'] = $this->input->post('tg_premium-' . $i, TRUE);
                $tertanggung['tg_beneficial'] = $this->input->post('tg_beneficial-' . $i, TRUE);
                $tertanggung['tg_aw_relation'] = $this->input->post('tg_aw_relation-' . $i, TRUE);
                $tertanggung['tg_address'] = $this->input->post('tg_address-' . $i, TRUE);
                $tertanggung['tg_zipcode'] = $this->input->post('tg_zipcode-' . $i, TRUE);
                $tertanggung['tg_kelurahan'] = $this->input->post('tg_kelurahan-' . $i, TRUE);
                $tertanggung['tg_kecamatan'] = $this->input->post('tg_kecamatan-' . $i, TRUE);
                $tertanggung['tg_city'] = $this->input->post('tg_city-' . $i, TRUE);
                $tertanggung['tg_rt'] = $this->input->post('tg_rt-' . $i, TRUE);
                $tertanggung['tg_rw'] = $this->input->post('tg_rw-' . $i, TRUE);
                $tertanggung['tg_activephone'] = $this->input->post('tg_activephone-' . $i, TRUE);
                $tertanggung['tg_occupation'] = $this->input->post('tg_occupation-' . $i, TRUE);
                $tertanggung['tg_relnumber'] = $i;
                $this->db->insert('tb_tertanggung', $tertanggung);

                ## Change Policy Number Staging Lock
                $update = array('staging' => 2);
                $this->db->where('policy_no', $tertanggung['tg_policy_no']);
                $this->db->update('tb_policyno', $update);
            }
        }

        ## Check Campaign Type
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1013',
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1013',
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1013', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'SPV'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => 0,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        $this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        $this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }

    function agree_ls($id_prospect, $id_product, $type = "")
    { // Agree NTB
        ## calltrack
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);
        $post['apl_giftcode'] = $this->input->post('apl_giftcode', TRUE);
        $post['apl_cardselect'] = $this->input->post('card_opt', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'NTB';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);

        ## Alamat Korespondensi Surat/Menyurat
        $post['bio_addr'] = $this->input->post('bio_addr', TRUE);
        $post['bio_nationopt'] = $this->input->post('bio_nationopt', TRUE);
        $post['bio_billzipcode'] = $this->input->post('bio_billzipcode', TRUE);
        $post['bio_billkelurahan'] = $this->input->post('bio_billkelurahan', TRUE);
        $post['bio_billkecamatan'] = $this->input->post('bio_billkecamatan', TRUE);
        $post['bio_billcity'] = $this->input->post('bio_billcity', TRUE);
        $post['bio_billrt'] = $this->input->post('bio_billrt', TRUE);
        $post['bio_billrw'] = $this->input->post('bio_billrw', TRUE);
        $post['bio_lasteduopt'] = $this->input->post('bio_lasteduopt', TRUE);
        $post['bio_lasteduopt'] == "OTHER" ? $post['bio_lastedu_other'] = $this->input->post('bio_lastedu_other', TRUE) : $post['bio_lastedu_other'] = NULL;
        $post['bio_dependent'] = $this->input->post('bio_dependent', TRUE);
        $post['billhome_opt'] = $this->input->post('billhome_opt', TRUE);
        $post['bio_billresideyear'] = $this->input->post('bio_billresideyear', TRUE);
        $post['bio_billresidemonth'] = $this->input->post('bio_billresidemonth', TRUE);
        $post['bio_billhomephonearea'] = $this->input->post('bio_billhomephonearea', TRUE);
        $post['bio_billhomephone'] = $this->input->post('bio_billhomephone', TRUE);
        $post['bio_billcellular'] = $this->input->post('bio_billcellular', TRUE);
        $post['bio_npwp'] = $this->input->post('bio_npwp', TRUE);
        $post['bio_email'] = $this->input->post('bio_email', TRUE);
        $post['bio_maidenname'] = $this->input->post('bio_maidenname', TRUE);

        ## Data Pekerjaan
        $post['ocp_businessline'] = $this->input->post('ocp_businessline', TRUE);
        $post['ocp_occupationopt'] = $this->input->post('ocp_occupationopt', TRUE);
        $post['ocp_occustatopt'] = $this->input->post('ocp_occustatopt', TRUE);
        $post['ocp_occupationopt'] == "OTHER" ? $post['ocp_occupation_other'] = $this->input->post('ocp_occupation_other', TRUE) : $post['ocp_occupation_other'] = NULL;
        $post['ocp_occutypeopt'] = $this->input->post('ocp_occutypeopt', TRUE);
        $post['ocp_occutypeopt'] == "OTHER" ? $post['ocp_occutype_other'] = $this->input->post('ocp_occutype_other', TRUE) : $post['ocp_occutype_other'] = NULL;
        $post['ocp_businessline'] = $this->input->post('ocp_businessline', TRUE);
        $post['ocp_companyname'] = $this->input->post('ocp_companyname', TRUE);
        $post['ocp_officeaddr'] = $this->input->post('ocp_officeaddr', TRUE);
        $post['ocp_kelurahan'] = $this->input->post('ocp_kelurahan', TRUE);
        $post['ocp_kecamatan'] = $this->input->post('ocp_kecamatan', TRUE);
        $post['ocp_rt'] = $this->input->post('ocp_rt', TRUE);
        $post['ocp_rw'] = $this->input->post('ocp_rw', TRUE);
        $post['ocp_zipcode'] = $this->input->post('ocp_zipcode', TRUE);
        $post['ocp_city'] = $this->input->post('ocp_city', TRUE);
        $post['ocp_kabupaten'] = $this->input->post('ocp_kabupaten', TRUE);
        $post['ocp_officephonearea'] = $this->input->post('ocp_officephonearea', TRUE);
        $post['ocp_officephone'] = $this->input->post('ocp_officephone', TRUE);
        $post['ocp_officeext'] = $this->input->post('ocp_officeext', TRUE);
        $post['ocp_fax'] = $this->input->post('ocp_fax', TRUE);
        $post['ocp_nik'] = $this->input->post('ocp_nik', TRUE);
        $post['ocp_empcount'] = $this->input->post('ocp_empcount', TRUE);
        $post['ocp_division'] = $this->input->post('ocp_division', TRUE);
        $post['ocp_department'] = $this->input->post('ocp_department', TRUE);
        $post['ocp_position'] = $this->input->post('ocp_position', TRUE);
        $post['ocp_yearduration'] = $this->input->post('ocp_yearduration', TRUE);
        $post['ocp_monthduration'] = $this->input->post('ocp_monthduration', TRUE);
        $post['ocp_lastyearduration'] = $this->input->post('ocp_lastyearduration', TRUE);
        $post['ocp_lastmonthduration'] = $this->input->post('ocp_lastmonthduration', TRUE);
        $post['ocp_additionalincome'] = $this->input->post('ocp_additionalincome', TRUE);
        $post['ocp_additionalsource'] = $this->input->post('ocp_additionalsource', TRUE);
        $post['ocp_lastyearusaha'] = $this->input->post('ocp_lastyearusaha', TRUE);
        $post['ocp_lastcompanyname'] = $this->input->post('ocp_lastcompanyname', TRUE);
        $post['ocp_email'] = $this->input->post('ocp_email', TRUE);
        ##penambahan
        $post['ocp_cc'] = $this->input->post('ocp_cc', TRUE);

        ## Data Keluarga
        $post['fam_fullname'] = $this->input->post('fam_fullname', TRUE);
        $post['fam_addr'] = $this->input->post('fam_addr', TRUE);
        $post['fam_homephonearea'] = $this->input->post('fam_homephonearea', TRUE);
        $post['fam_homephone'] = $this->input->post('fam_homephone', TRUE);
        $post['fam_cellular'] = $this->input->post('fam_cellular', TRUE);
        $post['fam_relationopt'] = $this->input->post('fam_relationopt', TRUE);
        $post['fam_relationopt'] == 'OTHER' ? $post['fam_relationoth'] = $this->input->post('fam_relationoth', TRUE) : $post['fam_relationoth'] = NULL;
        $post['fam_officephonearea'] = $this->input->post('fam_officephonearea', TRUE);
        $post['fam_officephone'] = $this->input->post('fam_officephone', TRUE);


        ## Payment Data
        $post['ocp_grossincome'] = $this->input->post('ocp_grossincome', TRUE);
        $post['bio_foreigntaxflag'] = $this->input->post('bio_foreigntaxflag', TRUE);
        $post['pjm_fullname'] = $this->input->post('pjm_fullname', TRUE);
        $post['pjm_resideyear'] = $this->input->post('pjm_resideyear', TRUE);
        $post['pjm_residemonth'] = $this->input->post('pjm_residemonth', TRUE);
        $post['pjm_year'] = $this->input->post('pjm_year', TRUE);
        $post['penagihan_add'] = $this->input->post('penagihan_add', TRUE);
        $post['ang_autodebet'] = $this->input->post('ang_autodebet', TRUE);
        $post['status_pembayaran'] = $this->input->post('status_pembayaran', TRUE);
        $post['ang_rekening'] = $this->input->post('ang_rekening', TRUE);
        $post['ang_cabang'] = $this->input->post('ang_cabang', TRUE);

        ## Payment Data
        $post['card_type'] = $this->input->post('card_type', TRUE);
        $post['card_holdername'] = $this->input->post('card_holdername', TRUE);
        $post['card_bank'] = $this->input->post('card_bank', TRUE);
        $post['card_bank1'] = $this->input->post('card_bank1', TRUE);
        $post['card_expirity'] = $this->input->post('card_expirity', TRUE);
        $post['card_number'] = $this->input->post('card_number', TRUE);
        $post['payment_source'] = $this->input->post('payment_source', TRUE);
        $post['question_add'] = $this->input->post('question_add', TRUE);

        ## Supplement card 1
        $post['sup_fullname'] = $this->input->post('sup_fullname', TRUE);
        $post['sup_embossname'] = $this->input->post('sup_embossname', TRUE);
        $post['sup_socialcode'] = $this->input->post('sup_socialcode', TRUE);
        $post['sup_negara'] = $this->input->post('sup_negara', TRUE);
        $post['sup_cellular'] = $this->input->post('sup_cellular', TRUE);
        $post['sup_homephonearea'] = $this->input->post('sup_homephonearea', TRUE);
        $post['sup_homephone'] = $this->input->post('sup_homephone', TRUE);
        $post['sup_identityopt'] = $this->input->post('sup_identityopt', TRUE);
        $post['sup_genderopt'] = $this->input->post('sup_genderopt', TRUE);
        $post['sup_dob'] = $this->input->post('sup_dob', TRUE) == 'YYYY-MM-DD' ? $post['sup_dob'] = NULL : $post['sup_dob'] = $this->input->post('sup_dob', TRUE);
        $post['sup_pob'] = $this->input->post('sup_pob', TRUE);
        $post['sup_maidenname'] = $this->input->post('sup_maidenname', TRUE);
        $post['sup_cardlimit'] = $this->input->post('sup_cardlimit', TRUE);
        $post['sup_relationopt'] = $this->input->post('sup_relationopt', TRUE);
        $post['sup_companyname'] = $this->input->post('sup_companyname', TRUE);
        $post['sup_businessline'] = $this->input->post('sup_businessline', TRUE);
        $post['sup_position'] = $this->input->post('sup_position', TRUE);

        ## Data Pickup
        $post['pickup_opt'] = $this->input->post('pickup_opt', TRUE);
        $post['pku_addr'] = $this->input->post('pku_addr', TRUE);
        $post['pku_zipcode'] = $this->input->post('pku_zipcode', TRUE);
        $post['pku_kelurahan'] = $this->input->post('pku_kelurahan', TRUE);
        $post['pku_kecamatan'] = $this->input->post('pku_kecamatan', TRUE);
        $post['pku_rt'] = $this->input->post('pku_rt', TRUE);
        $post['pku_rw'] = $this->input->post('pku_rw', TRUE);
        $post['pku_city'] = $this->input->post('pku_city', TRUE);
        $post['pku_kabupaten'] = $this->input->post('pku_kabupaten', TRUE);
        $post['pku_date'] = $this->input->post('pku_date', TRUE);
        $post['pku_date'] == '' ? $post['pku_date'] = NULL : $post['pku_date'] = $this->input->post('pku_date', TRUE);
        $post['pku_notes'] = $this->input->post('pku_notes', TRUE);
        $post['pku_zone'] = $this->input->post('zone_id', TRUE);
        $post['status_form'] = $this->input->post('status_form', TRUE);
        //$post['kurir_id'] = $this->assign_courier($post['pku_zone'], $post['pku_date']);
        $post['pku_courier'] = $this->assign_courier($post['pku_zone'], $post['pku_date']);
        $cardnumber = $this->input->post('cardnumber', TRUE);
        $post['cardnumber'] = $cardnumber;
        $post['tg_fullname'] = $this->input->post('tg_fullname', TRUE);
        $post['tg_social_number'] = $this->input->post('tg_social_number', TRUE);
        $post['tg_email'] = $this->input->post('tg_email', TRUE);
        $post['tg_dob'] = $this->input->post('tg_dob', TRUE);
        $post['tg_dob'] == 'YYYY-MM-DD' ? $post['tg_dob'] = '' : $post['tg_dob'] = $this->input->post('tg_dob', TRUE);
        $post['tg_pob'] = $this->input->post('tg_pob', TRUE);
        $post['tg_umur'] = $this->input->post('tg_umur', TRUE);
        $post['tg_gender'] = $this->input->post('tg_gender', TRUE);
        $post['tg_ch_relation'] = $this->input->post('tg_ch_relation', TRUE);
        $post['tg_marital_status'] = $this->input->post('tg_marital_status', TRUE);
        //$post['tg_plan'] = $this->input->post('tg_plan', TRUE);
        //$post['tg_premium'] = $this->input->post('tg_premium', TRUE);
        //$tertanggung['tg_beneficial'] = $this->input->post('tg_beneficial-'.$i, TRUE);
        //$tertanggung['tg_aw_relation'] = $this->input->post('tg_aw_relation-'.$i, TRUE);
        $post['tg_address'] = $this->input->post('tg_address', TRUE);
        $post['tg_zipcode'] = $this->input->post('tg_zipcode', TRUE);
        $post['tg_kelurahan'] = $this->input->post('tg_kelurahan', TRUE);
        $post['tg_kecamatan'] = $this->input->post('tg_kecamatan', TRUE);
        $post['tg_city'] = $this->input->post('tg_city', TRUE);
        $post['tg_rt'] = $this->input->post('tg_rt', TRUE);
        $post['tg_rw'] = $this->input->post('tg_rw', TRUE);
        //$post['tg_activephone'] = $this->input->post('tg_activephone', TRUE);
        $post['bio_titleafter'] = $this->input->post('bio_titleafter', TRUE);
        ## Ahli Waris
        $post['ben_name_1'] = $this->input->post('ben_name_1', TRUE);
        $post['ben_rel_1'] = $this->input->post('ben_rel_1', TRUE);
        $post['ben_name_2'] = $this->input->post('ben_name_2', TRUE);
        $post['ben_rel_2'] = $this->input->post('ben_rel_2', TRUE);
        $post['ben_name_3'] = $this->input->post('ben_name_3', TRUE);
        $post['ben_rel_3'] = $this->input->post('ben_rel_3', TRUE);

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();

        ## Insert Data Tertanggung

        /* $max_loop = 1; //LiveSmart cuma 1 tertanggung
        
        for($i=1;$i<=$max_loop;$i++){
            if($insert_id > 0){
                ## Data Tertanggung
                $tertanggung['print_idx'] = $insert_id;
                $tertanggung['prospect_id'] = $this->input->post('apl_appid' ,TRUE);
                $tertanggung['cardnumber'] = $cardnumber;
                //$tertanggung['tg_policy_no'] = $this->input->post('tg_policy_no-'.$i, TRUE);
                $tertanggung['tg_fullname'] = $this->input->post('tg_fullname-'.$i, TRUE);
                $tertanggung['tg_social_number'] = $this->input->post('tg_social_number-'.$i, TRUE);
                $tertanggung['tg_email'] = $this->input->post('tg_email-'.$i, TRUE);
                $tertanggung['tg_dob'] = $this->input->post('tg_dob-'.$i, TRUE);
                $tertanggung['tg_pob'] = $this->input->post('tg_pob-'.$i, TRUE);
                $tertanggung['tg_umur'] = $this->input->post('tg_umur-'.$i, TRUE);
                $tertanggung['tg_gender'] = $this->input->post('tg_gender-'.$i, TRUE);
                $tertanggung['tg_ch_relation'] = $this->input->post('tg_ch_relation-'.$i, TRUE);
                $tertanggung['tg_marital_status'] = $this->input->post('tg_marital_status-'.$i, TRUE);
                $tertanggung['tg_plan'] = $this->input->post('tg_plan-'.$i, TRUE);
                $tertanggung['tg_premium'] = $this->input->post('tg_premium-'.$i, TRUE);
                //$tertanggung['tg_beneficial'] = $this->input->post('tg_beneficial-'.$i, TRUE);
                //$tertanggung['tg_aw_relation'] = $this->input->post('tg_aw_relation-'.$i, TRUE);
                $tertanggung['tg_address'] = $this->input->post('tg_address-'.$i, TRUE);
                $tertanggung['tg_zipcode'] = $this->input->post('tg_zipcode-'.$i, TRUE);
                $tertanggung['tg_kelurahan'] = $this->input->post('tg_kelurahan-'.$i, TRUE);
                $tertanggung['tg_kecamatan'] = $this->input->post('tg_kecamatan-'.$i, TRUE);
                $tertanggung['tg_city'] = $this->input->post('tg_city-'.$i, TRUE);
                $tertanggung['tg_rt'] = $this->input->post('tg_rt-'.$i, TRUE);
                $tertanggung['tg_rw'] = $this->input->post('tg_rw-'.$i, TRUE);
                $tertanggung['tg_activephone'] = $this->input->post('tg_activephone-'.$i, TRUE);
                $tertanggung['tg_relnumber'] = $i;
                $this->db->insert('tb_tertanggung', $tertanggung);
            }
        }
        */
        ## Check Campaign Type
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1013',
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1013',
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1013', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'TSO'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => 0,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product,
            'outcall_id' => $this->input->post('outcall_id_submit'),
            'caller_id' => $this->input->post('caller_id_submit'),
            'outcall_start' => $this->input->post('outcall_start_submit'),
            'outcall_duration' => $this->input->post('outcall_sec_realtime_submit'),
            'rec_id' => $this->input->post('rec_id_submit'),
            'rec_filename' => $this->input->post('rec_filename_submit'),
            'end_call_time' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update dispo
        $this->load->model('curl_model');
        $this->load->model('misc/misc_model');
        $dispo = $this->misc_model->get_tableDataById('tb_callcode', 52, 'id_callcode', 'vcode');
        $dispo_nextpause = $this->input->post('dispo_nextpause');

        $targeturl = "http://" . ip_server() . "/vtel/index.php/api/dispo/submitDispo";
        $postdata = array(
            'session_name' => $_SESSION['session_name'],
            'outcall_id' => $this->input->post('outcall_id_submit'),
            'dispo_choice' => $dispo,
            'dispo_nextpause' => $dispo_nextpause == 'on' ? 'true' : 'false',
            'callback_dt' => ''
        );
        $curlResp = $this->curl_model->curlpost($targeturl, $postdata);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        //$this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        //$this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }


    function agree_casa($id_prospect, $id_product, $type = "")
    { // Agree CASA
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);
        $post['apl_cardselect'] = $this->input->post('card_opt', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'CASA';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);

        ## Alamat Korespondensi Surat/Menyurat
        $post['bio_cif'] = $this->input->post('bio_cif', TRUE);
        $post['ben_other'] = $this->input->post('ben_other', TRUE);
        $post['bio_lastedu_other'] = $this->input->post('bio_lastedu_other', TRUE);
        $post['bio_npwp'] = $this->input->post('bio_npwp', TRUE);
        $post['fun_npwp'] = $this->input->post('fun_npwp', TRUE);

        $post['tg_fullname'] = $this->input->post('tg_fullname', TRUE);
        $post['tg_social_number'] = $this->input->post('tg_social_number', TRUE);
        $post['tg_exp_card'] = $this->input->post('tg_exp_card', TRUE);
        $post['tg_creditlimit'] = $this->input->post('tg_creditlimit', TRUE);
        $post['tg_tjrek'] = $this->input->post('tg_tjrek', TRUE);
        $post['tg_sdn'] = $this->input->post('tg_sdn', TRUE);
        $post['tg_jmltransk'] = $this->input->post('tg_jmltransk', TRUE);
        $post['tg_dob'] = $this->input->post('tg_dob', TRUE);
        $post['tg_dob'] == 'YYYY-MM-DD' ? $post['tg_dob'] = '' : $post['tg_dob'] = $this->input->post('tg_dob', TRUE);
        $post['tg_pob'] = $this->input->post('tg_pob', TRUE);
        $post['tg_gender'] = $this->input->post('tg_gender', TRUE);
        $post['tg_relnumber'] = $this->input->post('tg_relnumber', TRUE);

        ## New Casa
        $changecategory = $this->input->post('tg_changecategory', TRUE);
        $post['tg_changecategory'] = $changecategory ? json_encode($changecategory) : "";
        $post['tg_branchcode'] = $this->input->post('tg_branchcode', TRUE);
        $post['tg_remark'] = $this->input->post('tg_remark', TRUE);

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();


        ## Check Campaign Type
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1015',  //Sent to QA
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1015',  //Sent to QA
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1015', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'TSO'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => 0,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product,
            'outcall_id' => $this->input->post('outcall_id_submit'),
            'caller_id' => $this->input->post('caller_id_submit'),
            'outcall_start' => $this->input->post('outcall_start_submit'),
            'outcall_duration' => $this->input->post('outcall_sec_realtime_submit'),
            'rec_id' => $this->input->post('rec_id_submit'),
            'rec_filename' => $this->input->post('rec_filename_submit'),
            'end_call_time' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update dispo
        $this->load->model('curl_model');
        $this->load->model('misc/misc_model');
        $dispo = $this->misc_model->get_tableDataById('tb_callcode', 52, 'id_callcode', 'vcode');
        $dispo_nextpause = $this->input->post('dispo_nextpause');

        $targeturl = "http://" . ip_server() . "/vtel/index.php/api/dispo/submitDispo";
        $postdata = array(
            'session_name' => $_SESSION['session_name'],
            'outcall_id' => $this->input->post('outcall_id_submit'),
            'dispo_choice' => $dispo,
            'dispo_nextpause' => $dispo_nextpause == 'on' ? 'true' : 'false',
            'callback_dt' => ''
        );
        $curlResp = $this->curl_model->curlpost($targeturl, $postdata);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        //$this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        //$this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }


    function agree_xtmrw($id_prospect, $id_product, $type = "")
    { // Agree xtmrw
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);
        $post['apl_cardselect'] = $this->input->post('card_opt', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'xTMRW';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);

        ## Alamat Korespondensi Surat/Menyurat
        $post['bio_cif'] = $this->input->post('bio_cif', TRUE);
        $post['ben_other'] = $this->input->post('ben_other', TRUE);
        $post['bio_lastedu_other'] = $this->input->post('bio_lastedu_other', TRUE);
        $post['bio_npwp'] = $this->input->post('bio_npwp', TRUE);
        $post['fun_npwp'] = $this->input->post('fun_npwp', TRUE);

        $post['tg_fullname'] = $this->input->post('tg_fullname', TRUE);
        $post['tg_social_number'] = $this->input->post('tg_social_number', TRUE);
        $post['tg_exp_card'] = $this->input->post('tg_exp_card', TRUE);
        $post['tg_creditlimit'] = $this->input->post('tg_creditlimit', TRUE);
        $post['tg_tjrek'] = $this->input->post('tg_tjrek', TRUE);
        $post['tg_sdn'] = $this->input->post('tg_sdn', TRUE);
        $post['tg_jmltransk'] = $this->input->post('tg_jmltransk', TRUE);
        $post['tg_dob'] = $this->input->post('tg_dob', TRUE);
        $post['tg_dob'] == 'YYYY-MM-DD' ? $post['tg_dob'] = '' : $post['tg_dob'] = $this->input->post('tg_dob', TRUE);
        $post['tg_pob'] = $this->input->post('tg_pob', TRUE);
        $post['tg_gender'] = $this->input->post('tg_gender', TRUE);
        $post['tg_relnumber'] = $this->input->post('tg_relnumber', TRUE);

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();


        ## Check Campaign Type
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1015',  //Sent to QA
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1015',  //Sent to QA
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1015', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'TSO'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => 0,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        //$this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        //$this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }
    function agree_stash($id_prospect, $id_product, $type = "")
    { // Agree stash
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);
        $post['apl_cardselect'] = $this->input->post('card_opt', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'STASH';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);

        ## Alamat Korespondensi Surat/Menyurat
        $post['bio_cif'] = $this->input->post('bio_cif', TRUE);
        $post['ben_other'] = $this->input->post('ben_other', TRUE);
        $post['bio_lastedu_other'] = $this->input->post('bio_lastedu_other', TRUE);
        $post['bio_npwp'] = $this->input->post('bio_npwp', TRUE);
        $post['fun_npwp'] = $this->input->post('fun_npwp', TRUE);

        $post['tg_fullname'] = $this->input->post('tg_fullname', TRUE);
        $post['tg_social_number'] = $this->input->post('tg_social_number', TRUE);
        $post['tg_exp_card'] = $this->input->post('tg_exp_card', TRUE);
        $post['tg_creditlimit'] = $this->input->post('tg_creditlimit', TRUE);
        $post['tg_tjrek'] = $this->input->post('tg_tjrek', TRUE);
        $post['tg_sdn'] = $this->input->post('tg_sdn', TRUE);
        $post['tg_jmltransk'] = $this->input->post('tg_jmltransk', TRUE);
        $post['tg_dob'] = $this->input->post('tg_dob', TRUE);
        $post['tg_dob'] == 'YYYY-MM-DD' ? $post['tg_dob'] = '' : $post['tg_dob'] = $this->input->post('tg_dob', TRUE);
        $post['tg_pob'] = $this->input->post('tg_pob', TRUE);
        $post['tg_gender'] = $this->input->post('tg_gender', TRUE);
        $post['tg_relnumber'] = $this->input->post('tg_relnumber', TRUE);

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();


        ## Check Campaign Type
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1015',  //Sent to QA
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1015',  //Sent to QA
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1015', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'TSO'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => 0,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        //$this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        //$this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }

    function agree_pc4($id_prospect, $id_product, $type = "")
    { // Agree Combo4
        ## calltrack
        $id_calltrack = $this->input->post('id_calltrack', TRUE);

        ## Application
        $post['apl_msc'] = $this->input->post('apl_msc', TRUE);
        $post['apl_prospectid'] = $this->input->post('apl_appid', TRUE);
        $post['apl_userid'] = $this->input->post('apl_sellercode', TRUE);
        $post['apl_productcode'] = $this->input->post('apl_productcode', TRUE);
        $post['bio_fullname'] = $this->input->post('bio_fullname', TRUE);
        $post['agree_notes'] = $this->input->post('agree_notes', TRUE);

        ## Campaign and Product
        $post['created_by'] = $_SESSION['id_user'];
        $post['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $post['id_product'] = $id_product;
        $post['tsr_id'] = $_SESSION['id_user'];
        $post['buy_type'] = 'PC4';
        $buy_camptype = $this->input->post('buy_camptype', TRUE);

        ## Alamat Korespondensi Surat/Menyurat
        $post['bio_addr'] = $this->input->post('bio_addr', TRUE);
        $post['bio_billzipcode'] = $this->input->post('bio_billzipcode', TRUE);
        $post['bio_billkelurahan'] = $this->input->post('bio_billkelurahan', TRUE);
        $post['bio_billkecamatan'] = $this->input->post('bio_billkecamatan', TRUE);
        $post['bio_billcity'] = $this->input->post('bio_billcity', TRUE);
        $post['bio_billrt'] = $this->input->post('bio_billrt', TRUE);
        $post['bio_billrw'] = $this->input->post('bio_billrw', TRUE);

        ## Additional Data
        $post['ocp_grossincome'] = $this->input->post('ocp_grossincome', TRUE);
        $post['bio_foreigntaxflag'] = $this->input->post('bio_foreigntaxflag', TRUE);

        ## Payment Data
        $post['card_type'] = $this->input->post('card_type', TRUE);
        $post['card_holdername'] = $this->input->post('card_holdername', TRUE);
        $post['card_bank'] = $this->input->post('card_bank', TRUE);
        $post['card_expirity'] = $this->input->post('card_expirity', TRUE);
        $post['card_number'] = $this->input->post('card_number', TRUE);
        $post['payment_source'] = $this->input->post('payment_source', TRUE);

        ## Ahli Waris
        $post['ben_name_1'] = $this->input->post('ben_name_1', TRUE);
        $post['ben_rel_1'] = $this->input->post('ben_rel_1', TRUE);
        $post['ben_name_2'] = $this->input->post('ben_name_2', TRUE);
        $post['ben_rel_2'] = $this->input->post('ben_rel_2', TRUE);
        $post['ben_name_3'] = $this->input->post('ben_name_3', TRUE);
        $post['ben_rel_3'] = $this->input->post('ben_rel_3', TRUE);

        ## Insert Data for Printing.
        $this->db->insert('tb_prospect_print', $post);
        $insert_id = $this->db->insert_id();

        ## Insert Data Tertanggung
        $cardnumber = $this->input->post('cardnumber', TRUE);
        $max_loop = 1; //LiveSmart cuma 1 tertanggung

        for ($i = 1; $i <= $max_loop; $i++) {
            if ($insert_id > 0) {
                ## Data Tertanggung
                $tertanggung['print_idx'] = $insert_id;
                $tertanggung['prospect_id'] = $this->input->post('apl_appid', TRUE);
                $tertanggung['cardnumber'] = $cardnumber;
                //$tertanggung['tg_policy_no'] = $this->input->post('tg_policy_no-'.$i, TRUE);
                $tertanggung['tg_fullname'] = $this->input->post('tg_fullname-' . $i, TRUE);
                $tertanggung['tg_social_number'] = $this->input->post('tg_social_number-' . $i, TRUE);
                $tertanggung['tg_email'] = $this->input->post('tg_email-' . $i, TRUE);
                $tertanggung['tg_dob'] = $this->input->post('tg_dob-' . $i, TRUE);
                $tertanggung['tg_pob'] = $this->input->post('tg_pob-' . $i, TRUE);
                $tertanggung['tg_umur'] = $this->input->post('tg_umur-' . $i, TRUE);
                $tertanggung['tg_gender'] = $this->input->post('tg_gender-' . $i, TRUE);
                $tertanggung['tg_ch_relation'] = $this->input->post('tg_ch_relation-' . $i, TRUE);
                $tertanggung['tg_marital_status'] = $this->input->post('tg_marital_status-' . $i, TRUE);
                $tertanggung['tg_plan'] = $this->input->post('tg_plan-' . $i, TRUE);
                $tertanggung['tg_premium'] = $this->input->post('tg_premium-' . $i, TRUE);
                //$tertanggung['tg_beneficial'] = $this->input->post('tg_beneficial-'.$i, TRUE);
                //$tertanggung['tg_aw_relation'] = $this->input->post('tg_aw_relation-'.$i, TRUE);
                $tertanggung['tg_address'] = $this->input->post('tg_address-' . $i, TRUE);
                $tertanggung['tg_zipcode'] = $this->input->post('tg_zipcode-' . $i, TRUE);
                $tertanggung['tg_kelurahan'] = $this->input->post('tg_kelurahan-' . $i, TRUE);
                $tertanggung['tg_kecamatan'] = $this->input->post('tg_kecamatan-' . $i, TRUE);
                $tertanggung['tg_city'] = $this->input->post('tg_city-' . $i, TRUE);
                $tertanggung['tg_rt'] = $this->input->post('tg_rt-' . $i, TRUE);
                $tertanggung['tg_rw'] = $this->input->post('tg_rw-' . $i, TRUE);
                $tertanggung['tg_activephone'] = $this->input->post('tg_activephone-' . $i, TRUE);
                $tertanggung['tg_relnumber'] = $i;
                $this->db->insert('tb_tertanggung', $tertanggung);
            }
        }

        ## Check Campaign Type
        $this->load->model('misc/misc_model');
        $campaign_type = $this->misc_model->get_tableDataById('tb_campaign', $this->input->post('id_campaign', TRUE), 'id_campaign', 'campaign_type');

        ## Update Status Hold SPV
        if ($campaign_type != '0') {
            $update = array(
                'spv_holdstatus' => '1013',
                'spv_holddatetime' => DATE('Y-m-d H:i:s'),
                'cur_lockstage' => '1013',
                'cur_lockdatetime' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('idx', $insert_id);
            $this->db->update('tb_prospect_print', $update);

            $statustrack = array(
                'id_prospect' => $post['apl_prospectid'],
                'print_idx' => $insert_id,
                'status_code' => '1013', //STATUS nya Hold by SPV
                'update_by' => $_SESSION['id_user'],
                'update_type' => 'SPV'
            );
            $this->db->insert('tb_statustrack', $statustrack);
        }

        ## Set Agree
        $this->prospect_model->set_is_agree($id_prospect, $id_product);

        ## Insert Data For QA
        $qa_row = array(
            'id_prospect' => $id_prospect,
            'id_tsr' => $_SESSION['id_user'],
            'id_qa' => 0,
            'id_campaign' => $this->input->post('id_campaign', TRUE),
            'id_product' => $id_product,
            'qa_date' => NULL,
            'emr_hold' => 0,
            'no_telp' => $this->input->post('no_contacted', TRUE),
            'user_created' => $_SESSION['id_user'],
            'date_created' => DATE('Y-m-d')
        );
        $this->db->insert('tb_qa', $qa_row);

        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 52, //52 == agree
            'remark' => $post['agree_notes'],
            'id_product' => $id_product
        );
        $this->db->where('id_calltrack', $id_calltrack);
        $this->db->update('tb_calltrack', $calltrackData);

        ## Update Lastcall
        $lastcall_data = array(
            'last_id_callcode' => 52,
            'last_remark' => $this->input->post('agree_notes', TRUE),
            'last_calltime' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);
        $this->db->update('tb_prospect', $lastcall_data);

        ## Update NewPriority
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);

        ## Check apa data ini harus diset sebagai random listen
        //$this->generate_randomcheck($_SESSION['id_user'], $id_prospect);
        //$this->check_holdedrandom($_SESSION['id_user'], $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        redirect('tsr/main');
        exit;
    }


    public function agree_fax($id_prospect, $id_product = '')
    {
        $data["txt_title"] = "Agree " . $id_prospect;
        $post = @$_POST['post'];

        //var_dump($_POST);
        //die();

        // save detail product
        if ($post) {

            $post = array();
            foreach ($_POST as $key => $value)
                $post[$key] = $this->input->xss_clean($value);

            $arrdata = $post;
            $arrdata['id_user'] = $this->id_user;
            $arrdata['id_prospect'] = $id_prospect;
            $arrdata['created_date'] = date("Y-m-d") . ' ' . date("H:i:s");

            //$faxno = $arrdata['fax_number'];
            $jenis_aplikasi = $arrdata['is_fax'];

            //$id_callcode = $id_callcode ? $id_callcode : 'null';


            //prospect print
            //var_dump($arrdata['is_fax']);

            //echo " isi iiiiiiiiiii arr data == >>>>>>>> ".$arrdata ['fax_number'];
            //die();

            //prospect print
            //var_dump($arrdata);
            //die();
            $this->prospect_model->add_prospect_print($arrdata);
            $this->prospect_model->add_calltrack($id_prospect, '', $this->id_user, 36, '', date("Y-m-d"), date("H:i:s"), '', '', '', '', '', '', '', '');
            $this->prospect_model->set_is_agree($id_prospect, $id_product);
            $this->prospect_model->winfax($id_prospect, $jenis_aplikasi);


            //die("dorr");

            //redirect('tsr/main');
            // yg dipake di agree referral redirect("tsr/main_print/$id_prospect");
            /*
           //cek cttype kalo 3 berarti incoming
           if()
           {
           }
 */

            //$sql=$this->db->query("select *, a.id_prospect, a.fullname from tb_campaign as b inner join tb_prospect as a on a.id_campaign = b.id_campaign");
            $sql = $this->db->query("select a.id_prospect, b.campaign_type, a.fullname from tb_campaign as b
            											inner join tb_prospect as a on a.id_campaign = b.id_campaign 
            											where a.id_prospect=$id_prospect");
            $row = $sql->row_array();
            $data = $row["campaign_type"];
            //var_dump($data);
            //die();
            if ($data == 3) {
                $record_close->id_app_status = 4;
                $this->db->update($this->tb_prospect, $record_close, array('id_prospect' => $id_prospect));
                //var_dump($this->db->last_query());
                //die();
                // echo("dididi");
                redirect("tsr/main_print/$id_prospect");
            } else {
                //die("dododo");
                redirect("tsr/main_print/$id_prospect");
            }

            //var_dump($this->db->last_query());
            //die();
        } else {
            $data["id_product"] = $id_product;

            //get prospect
            $q = $this->db->query("select * from tb_prospect where id_prospect=$id_prospect");
            $row_prospect = $q->row_array();
            $data["row_prospect"] = $row_prospect;
        }

        //script tele
        $sql = "select * from tb_product where published=1";
        $q = $this->db->query($sql);

        $row_script = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;


        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/agree_fax', $data);
        $this->load->view('tsr/footer', $data);
    }


    //flow prospect referral
    public function agree_referral($id_prospect, $id_product = '')
    {
        $data["txt_title"] = "Agree " . $id_prospect;
        $post = @$_POST['post'];

        //var_dump($_POST);
        //die('');

        // save detail product
        if ($post) {



            $post = array();


            foreach ($_POST as $key => $value)
                $post[$key] = $this->input->xss_clean($value);

            //var_dump($_POST);

            $arrdata = $post;
            $arrdata['id_user'] = $this->id_user;
            $arrdata['id_prospect'] = $id_prospect;
            $arrdata['created_date'] = date("Y-m-d") . ' ' . date("H:i:s");
            //$pengiriman = $arrdata['pengiriman'];
            //$nama = $arrdata['nama'];

            //echo $pengiriman;

            //var_dump($arrdata);

            $id_user = $arrdata['id_user'];
            //die();





            //$faxno = '9'.$arrdata['fax_number'].'#456798';						
            //$faxno = $arrdata['fax_number'];
            //$mailto = $arrdata['mailto'];
            $jenis_aplikasi = $arrdata['is_fax'];

            // get fullname
            $sql3 = "select * from tb_prospect where id_prospect = '$id_prospect'";
            //echo $sql3;
            $q3 = $this->db->query($sql3);
            $data1 = $q3->row_array();

            $fullname = $data1['fullname'];
            $id_spv = $data1['id_spv'];


            //$this->prospect_model->get_fullname($id_prospect);



            //prospect print


            //echo 'fax_numberrrrrrr'$faxno;



            //

            $this->prospect_model->add_prospect_print($arrdata);
            $this->prospect_model->add_calltrack($id_prospect, '', $this->id_user, 14, '', date("Y-m-d"), date("H:i:s"), '', '', '', '', '', '', '', '');
            $this->prospect_model->set_is_agree($id_prospect, $id_product);
            //insert winfax



            $fax_number = $arrdata['fax_number'];
            $mailto = $arrdata['mailto'];

            if (TRIM($fax_number) != '') {
                $this->prospect_model->tb_winfax($id_prospect, $jenis_aplikasi, $fullname, $fax_number, $id_user, $id_spv);
            }

            if (TRIM($mailto) != '') {
                //insert email            
                $this->prospect_model->tb_email($id_prospect, $jenis_aplikasi, $fullname, $mailto, $id_user, $id_spv);
            }

            /*
            if($pengiriman==1){
							//echo "masuk pengiriman 1";
							//echo $nama;
							$this->prospect_model->tb_winfax($id_prospect,$jenis_aplikasi,$fullname,$nama,$id_user);
						}
						
						else{
							//echo "masuk pengiriman 2";
							$this->prospect_model->tb_email($id_prospect,$jenis_aplikasi,$fullname,$nama,$id_user);
						}
            */





            //die("dorr");

            //redirect('tsr/main');
            // yg dipake di agree referral redirect("tsr/main_print/$id_prospect");
            /*
           //cek cttype kalo 3 berarti incoming
           if()
           {
           }
 */

            //$sql=$this->db->query("select *, a.id_prospect, a.fullname from tb_campaign as b inner join tb_prospect as a on a.id_campaign = b.id_campaign");
            $sql = $this->db->query("select a.id_prospect, b.campaign_type, a.fullname from tb_campaign as b
            											inner join tb_prospect as a on a.id_campaign = b.id_campaign 
            											where a.id_prospect=$id_prospect");
            $row = $sql->row_array();
            $data = $row["campaign_type"];
            //var_dump($data);
            //die();
            if ($data == 3) {
                $record_close->id_app_status = 4;
                $this->db->update($this->tb_prospect, $record_close, array('id_prospect' => $id_prospect));
                //var_dump($this->db->last_query());
                //die();
                // echo("dididi");
                redirect("tsr/main_print/$id_prospect");
            } else {
                //die("dododo");
                redirect("tsr/main_print/$id_prospect");
            }

            //var_dump($this->db->last_query());
            //die();
        } else {
            $data["id_product"] = $id_product;

            //get prospect
            $q = $this->db->query("select * from tb_prospect where id_prospect=$id_prospect");
            $row_prospect = $q->row_array();
            $data["row_prospect"] = $row_prospect;
        }

        //script tele
        $sql = "select * from tb_product where published=1";
        $q = $this->db->query($sql);

        $row_script = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;

        //script tele 2	

        $sql1 = "select * from tb_script group by jenis_dokumen";

        $q1 = $this->db->query($sql1);



        $row_script1 = array();
        if ($q1->num_rows() > 0) {
            foreach ($q1->result_array() as $row1) {
                $row_script1[] = $row1;
            }
        }

        $data['scripts1'] = $row_script1;

        //script tele 2	


        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/agree_referral', $data);
        $this->load->view('tsr/footer', $data);
    }


    public function agree_referral_1($id_prospect, $id_product = '')
    {
        //die('msk agree_ref_1');
        $data["txt_title"] = "Agree " . $id_prospect;
        $post = @$_POST['post'];

        //var_dump($_POST);
        //die();


        // save detail product
        if ($post) {


            //die('msk post');




            $post = array();
            foreach ($_POST as $key => $value)
                $post[$key] = strtoupper($this->input->xss_clean($value));

            $arrdata = $post;
            $arrdata['id_user'] = $this->id_user;
            $arrdata['id_prospect'] = $id_prospect;
            $arrdata['created_date'] = date("Y-m-d") . ' ' . date("H:i:s");

            //prospect print
            //var_dump($arrdata);
            //die();
            $this->prospect_model->add_prospect_print($arrdata);
            $this->prospect_model->add_calltrack($id_prospect, '', $this->id_user, 14, '', date("Y-m-d"), date("H:i:s"), '', '', '', '', '', '', '', '');
            $this->prospect_model->set_is_agree($id_prospect, $id_product);

            //die("dorr");

            //redirect('tsr/main');
            // yg dipake di agree referral redirect("tsr/main_print/$id_prospect");
            /*
           //cek cttype kalo 3 berarti incoming
           if()
           {
           }
 */



            //$sql=$this->db->query("select *, a.id_prospect, a.fullname from tb_campaign as b inner join tb_prospect as a on a.id_campaign = b.id_campaign");
            $sql = $this->db->query("select a.id_prospect, b.campaign_type, a.fullname from tb_campaign as b
            											inner join tb_prospect as a on a.id_campaign = b.id_campaign
            											where a.id_prospect=$id_prospect");
            $row = $sql->row_array();
            $data = $row["campaign_type"];
            //var_dump($data);
            //die();
            if ($data == 3) {
                $record_close->id_app_status = 4;
                $this->db->update($this->tb_prospect, $record_close, array('id_prospect' => $id_prospect));
                //var_dump($this->db->last_query());
                //die();
                // echo("dididi");
                redirect("tsr/main_print/$id_prospect");
            } else {
                //die("dododo");
                redirect("tsr/main_print/$id_prospect");
            }

            //var_dump($this->db->last_query());
            //die();
        } else {
            $data["id_product"] = $id_product;

            //get prospect
            $q = $this->db->query("select * from tb_prospect where id_prospect=$id_prospect");
            $row_prospect = $q->row_array();
            $data["row_prospect"] = $row_prospect;
        }

        $data1 = array();

        $sql = "select tpp.* from tb_prospect tpp
										where tpp.id_prospect=?";
        $q = $this->db->query($sql, array("id_prospect" => $id_prospect));
        //var_dump($this->db->last_query());
        //die();
        $data_res = null;
        if ($q->num_rows() > 0) {
            $data_res = $q->row_array();
        }
        $q->free_result();

        $data1 = array(
            "row1" => $data_res
        );


        //script tele
        $sql = "select * from tb_product where published=1";
        $q = $this->db->query($sql);

        $row_script = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;
        $data['prospect_det'] = $data1;

        //script tele 2	

        $sql1 = "select * from tb_script group by jenis_dokumen";

        $q1 = $this->db->query($sql1);



        $row_script1 = array();
        if ($q1->num_rows() > 0) {
            foreach ($q1->result_array() as $row1) {
                $row_script1[] = $row1;
            }
        }

        $data['scripts1'] = $row_script1;

        //script tele 2	

        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/agree_referral_1', $data);
        $this->load->view('tsr/footer', $data);
    }

    public function main_print($id_prospect)
    {
        $data = array(
            "txt_title" => "Print Customer",
            "id_prospect" => $id_prospect
        );

        //script tele
        $sql = "select * from tb_product where published=1";
        $q = $this->db->query($sql);

        $row_script = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;

        $this->load->view("tsr/header", $data);
        $this->load->view("tsr/main_print", $data);
        $this->load->view('tsr/footer', $data);
    }


    public function print_prospect($id_prospect)
    {

        $data = array();

        $sql = "select * from tb_prospect_print where id_prospect=?";
        $q = $this->db->query($sql, array("id_prospect" => $id_prospect));

        $data_res = null;
        if ($q->num_rows() > 0) {
            $data_res = $q->row_array();
        }
        $q->free_result();

        $data = array(
            "row" => $data_res
        );

        //var_dump($data_res);
        //die();

        $this->load->view('admin/printout', $data);
    }


    public function list_calltrack()
    {
        $id_prospect = $this->uri->segment(3);
        $id_product = $this->uri->segment(4);
        $data["list_code"] = $this->callcode_model->get_list_callcode('parent_id_callcode="0"');
        $data["list"] = $this->prospect_model->get_list_calltrack($id_prospect, $this->id_user, $id_product);
        $data["id_prospect"] = $id_prospect;
        $data["id_product"] = $id_product;
        $data["phone_home"] = $this->prospect_model->get_detail_prospect($id_prospect, 'phone_home');
        $data["phone_office"] = $this->prospect_model->get_detail_prospect($id_prospect, 'phone_office');
        $data["phone_mobile"] = $this->prospect_model->get_detail_prospect($id_prospect, 'phone_mobile');
        $this->load->view('tsr/call_track', $data);
    }

    public function submit_status($id_prospect)
    {
        $id_app_status = $_POST['id_app_status'];
        if ($this->prospect_model->set_status($id_prospect, $id_app_status)) {
            $data['prospect']['id_app_status'] = $id_app_status;
            echo $this->load->view('tsr/submit_status', $data, true);
        }
    }

    /** Simpan Calltrack **/ ##-> modified by martin

    public function submit_calltrack($parent_id = '', $id_prospect = '')
    {
        if($_SERVER["REMOTE_ADDR"] == '10.100.1.254')
        {
            echo $this->output->enable_profiler(true);
        }

        $id_campaign = $this->input->post('id_campaign', TRUE);

        $id_prospect = $id_prospect ? $id_prospect : $this->uri->segment(3);
        $remark = @$_POST["remark"];
        $id_callcode = @$_POST["id_callcode"] ? $_POST["id_callcode"] : 0;
        ## Kalau Reminder
        /*
         $id_callcode == '76' ? $reminder_date = $_POST["reminder_date"] : $reminder_date = NULL;
         $id_callcode == '76' ? $reminder_time = $_POST["reminder_time"].'.00' : $reminder_time = NULL;
         $id_callcode == '98' ? $reminder_date = $_POST["reminder_date"] : $reminder_date = NULL;
         $id_callcode == '98' ? $reminder_time = $_POST["reminder_time"].'.00' : $reminder_time = NULL;
        */
        if ($id_callcode == '76' || $id_callcode == '98') {
            $reminder_date = $_POST["reminder_date"];
            $reminder_time = $_POST["reminder_time"] . ':00';
        } else {
            $reminder_date = NULL;
            $reminder_time = NULL;
        }

        ## End Kalau Reminder
        $notelp = 0;
        $begin_call_time = @$_POST["begin_call_time"];
        $idle_time = @$_POST["idle_call_time"];
        $end_call_time = @$_POST["end_call_time"];
        $total_call_time = @$_POST["total_call_time"];
        $product_id = @$_POST['product_id'];
        $date = date("Y-m-d H:i:s");
        $calltrack_id = @$_POST['calltrack_id'];
        $id_line_call = @$_POST['id_line_call'];
        $context = @$_POST['context'];

        if (!empty($calltrack_id)) {
            if (substr($calltrack_id, 0, 2) != '08') {
                $update = array(
                    'remark' => $remark,
                    'id_callcode' => $id_callcode,
                    'id_product' => $product_id,
                    'outcall_id' => $this->input->post('outcall_id_submit'),
                    'caller_id' => $this->input->post('caller_id_submit'),
                    'outcall_start' => $this->input->post('outcall_start_submit'),
                    'outcall_duration' => $this->input->post('outcall_sec_realtime_submit'),
                    'rec_id' => $this->input->post('rec_id_submit'),
                    'rec_filename' => $this->input->post('rec_filename_submit'),
                    'end_call_time' => DATE('Y-m-d H:i:s')
                );

                $this->db->where('id_calltrack', $calltrack_id);
                $this->db->update('tb_calltrack', $update);
            } else {
                $update = array(
                    'remark' => $remark,
                    'id_callcode' => $id_callcode,
                    'id_product' => $product_id,
                    'outcall_id' => $this->input->post('outcall_id_submit'),
                    'caller_id' => $this->input->post('caller_id_submit'),
                    'outcall_start' => $this->input->post('outcall_start_submit'),
                    'outcall_duration' => $this->input->post('outcall_sec_realtime_submit'),
                    'rec_id' => $this->input->post('rec_id_submit'),
                    'rec_filename' => $this->input->post('rec_filename_submit'),
                    'end_call_time' => DATE('Y-m-d H:i:s')
                );
                //$this->db->where('id_calltrack', $calltrack_id);
                $where = "id_calltrack = '{$calltrack_id}'";
                $update_str = $this->db->update_string('tb_calltrack', $update, $where);

                $insert = array(
                    'update_str' => $update_str,
                    'captanggal' => date('Y-m-d H:i:s')
                );
                $this->db->insert('tb_calltrack_debug', $insert);
            }
        }

        ## Update dispo
        $this->load->model('curl_model');
        $this->load->model('misc/misc_model');
        $dispo = $this->misc_model->get_tableDataById('tb_callcode', $id_callcode, 'id_callcode', 'vcode');
        $dispo_nextpause = $this->input->post('dispo_nextpause');

            
            $targeturl = "http://" . ip_server() . "/vtel/index.php/api/dispo/submitDispo";
            $postdata = array(
                'session_name' => $_SESSION['session_name'],
                'outcall_id' => $this->input->post('outcall_id_submit'),
                'dispo_choice' => $dispo,
                'dispo_nextpause' => $dispo_nextpause == 'on' ? 'true' : 'false',
                'callback_dt' => $dispo == 'CBHOLD' ? $reminder_date . ' ' . $reminder_time : ''
            );
            $curlResp = $this->curl_model->curlpost($targeturl, $postdata);

            ## update ready
            $targeturl_cmd = "http://" . ip_server() . "/vtel/index.php/api/agent/set_agentReady";
            if ($dispo_nextpause != 'on') {
                $postdata_cmd = array(
                    'session_name' => $_SESSION['session_name']
                );
                $this->curl_model->curlpost($targeturl_cmd, $postdata_cmd);
            }
        
        
        if ($id_callcode == '76' || $id_callcode == '98') {
            ## remove old reminder
            $setData = array(
                'viewed' => 1,
            );
            $this->db->where('id_user', $this->id_user);
            $this->db->where('id_prospect', $id_prospect);
            $qObj = $this->db->update('tb_reminder', $setData);

            ## Add to reminder list
            $reminder = array(
                'id_prospect' => $id_prospect,
                'id_user' => $this->id_user,
                're_date' => $reminder_date,
                're_time' => $reminder_time,
                'remark' => $remark
            );
            $this->db->insert('tb_reminder', $reminder);
        }

        $this->load->model('calllimit_model');
        $this->update_newpriority($id_prospect);
        $this->update_recycle($id_prospect);
        $this->calllimit_model->callLock_logic($parent_id, $id_prospect);

        ## Clear Call Session
        $this->load->model('callsession_model');
        $this->callsession_model->clearCallsession();

        $arr_codecallcode = $this->callcode_model->get_list_callcode('id_callcode="' . $id_callcode . '"');
        if ($this->prospect_model->add_calltrack($id_prospect, $notelp, $this->id_user, $id_callcode, '', date("Y-m-d"), date("H:i:s"), '', $remark, '', '', $begin_call_time, $end_call_time, $idle_time, $total_call_time, $id_line_call, $context)) {
            // close prospect with special idcallcode
            redirect('tsr_v2/main/');
        } else {
            $data = "";
        }
        redirect('tsr_v2/main/');
        exit;
    }

    public function reminder($page = '', $pageValue = '')
    {
        $pageValue = $pageValue ? $pageValue : 1;
        $item_on_page = 5;
        $count = $this->reminder_model->get_count_reminder($this->id_user);
        $data["page"] = makePagingPage($item_on_page, $count, $pageValue, site_url() . '/tsr/reminder//page/', true, 'cont-reminder');

        $data["list"] = $this->reminder_model->get_list_reminder($this->id_user, '', $pageValue, $item_on_page);
        if ($page) {
            $this->load->view('tsr/list_reminder', $data);
        } else {
            return $this->load->view('tsr/list_reminder', $data, true);
        }
    }

    public function agree_prospect($id_campaign = '', $page = '', $pageValue = '')
    {
        $con_campaign = $id_campaign == '' || $id_campaign == 'null' ? '' : ' AND tp.id_campaign="' . $id_campaign . '"';

        $pageValue = $pageValue ? $pageValue : 1;
        $item_on_page = 10;
        $count = $this->prospect_model->get_count_agree_prospect(' id_tsr="' . $this->id_user . '" ' . $con_campaign);
        $data["page"] = makePagingPage($item_on_page, $count, $pageValue, site_url() . '/tsr/agree_prospect/' . $id_campaign . '/page/', true, 'cont-agree');

        $data['list'] = $this->prospect_model->get_list_agree_prospect(' id_tsr="' . $this->id_user . '" ' . $con_campaign, $pageValue, $item_on_page);
        $data['model_prospect'] = $this->prospect_model;

        if (!$page) {
            return $this->load->view('tsr/list_agree', $data, true);
        } else {
            $this->load->view('tsr/list_agree', $data);
        }
    }

    public function pickup_prospect()
    {
        //var_dump($_POST);
        //die();

        //remark pickup
        $id_prospect = $_POST['id_prospect'] ? $_POST['id_prospect'] : '';
        $no_contacted = $_POST['contacted'] ? $_POST['contacted'] : '';
        $remark = $_POST['remark'] ? $_POST['remark'] : '';
        $supp_name1 = $_POST['supp_name1'] ? $_POST['supp_name1'] : '';
        $supp_ttl1 = $_POST['supp_ttl1'] ? $_POST['supp_ttl1'] : '';
        $supp_dob1 = $_POST['supp_dob1'] ? $_POST['supp_dob1'] : '';
        $hub_supp1 = $_POST['hub_supp1'] ? $_POST['hub_supp1'] : '';
        $supp_mmn1 = $_POST['supp_mmn1'] ? $_POST['supp_mmn1'] : '';

        $supp_name2 = $_POST['supp_name2'] ? $_POST['supp_name2'] : '';
        $supp_ttl2 = $_POST['supp_ttl2'] ? $_POST['supp_ttl2'] : '';
        $supp_dob2 = $_POST['supp_dob2'] ? $_POST['supp_dob2'] : '';
        $hub_supp2 = $_POST['hub_supp2'] ? $_POST['hub_supp2'] : '';
        $supp_mmn2 = $_POST['supp_mmn2'] ? $_POST['supp_mmn2'] : '';

        $supp_name3 = $_POST['supp_name3'] ? $_POST['supp_name3'] : '';
        $supp_ttl3 = $_POST['supp_ttl3'] ? $_POST['supp_ttl3'] : '';
        $supp_dob3 = $_POST['supp_dob3'] ? $_POST['supp_dob3'] : '';
        $hub_supp3 = $_POST['hub_supp3'] ? $_POST['hub_supp3'] : '';
        $supp_mmn3 = $_POST['supp_mmn3'] ? $_POST['supp_mmn3'] : '';

        $notes_1 = $_POST['notes_1'] ? $_POST['notes_1'] : '';
        $notes_2 = $_POST['notes_2'] ? $_POST['notes_2'] : '';
        $notes_3 = $_POST['notes_3'] ? $_POST['notes_3'] : '';


        $kode_pos = $_POST['kode_pos'] ? $_POST['kode_pos'] : '';
        $catatan_1 = $_POST['catatan_1'] ? $_POST['catatan_1'] : '';
        $catatan_2 = $_POST['catatan_2'] ? $_POST['catatan_2'] : '';

        $parent_id = $_POST['parent_id'] ? $_POST['parent_id'] : '';

        $region = @$_POST["region"];
        $last_id_callcode = 0;
        $sql3 = "select last_id_callcode from tb_prospect where id_prospect='$id_prospect'";
        $q3 = $this->db->query($sql3);
        if ($q3->num_rows() > 0) {
            $data = $q3->row_array();
            $last_id_callcode = $data["last_id_callcode"];
        }
        if ($last_id_callcode == "14") {
            $sql2 = "select id_prospect from tb_delivery where id_prospect='$id_prospect'";
            $q2 = $this->db->query($sql2);
            if ($q2->num_rows() == 0) {
                $kurir = $this->notelp_model->get_courier_name($region);
                $barcode = "";
                $j = strlen($id_prospect);
                for ($i = 10; $j < $i; $j++) {
                    $barcode .= '0';
                }
                $barcode .= $id_prospect . date('Ymd');

                $data = array(
                    "id_prospect"         => $id_prospect,
                    "created"             => date("Y-m-d H:i:s"),
                    "is_status"         => 1,
                    "followup"             => 1,
                    "barcode"             => $barcode,
                    "is_call"             => 0,
                    "is_close"             => 1,
                    "closing_date"         => date('Y-m-d H:i:S'),
                    "is_active"            => 0,
                    "region"            => $region,
                    "pickup_address"    => $remark,
                    "kode_pos"            => $kode_pos,
                    "catatan_1"            => $catatan_1,
                    "catatan_2"            => $catatan_2,
                    "courier_name"        => $kurir
                );
                $this->db->insert("tb_delivery", $data);

                $sql = "insert into tb_calltrack_pod(id_prospect,is_status,status_pod,created,barcode) 
								select id_prospect,1,1,date(now()),barcode  
								from tb_delivery 
								where id_prospect in($id_prospect) ";
                $this->db->query($sql);

                //reset status
                $where = array(
                    "id_prospect" => $id_prospect,
                    "id_user" => $this->id_user,
                );
                $data_toupdate = array(
                    "is_active" => "0"
                );
                $this->db->update("tb_prospect_pickup", $data_toupdate, $where);

                //add new record
                $data = array(
                    "id_prospect" => $id_prospect,
                    "id_user" => $this->id_user,
                    "remark" => $remark,
                    "supp_name1" => $supp_name1,
                    "supp_ttl1" => $supp_ttl1,
                    "supp_dob1" => $supp_dob1,
                    "hub_supp1" => $hub_supp1,
                    "supp_mmn1" => $supp_mmn1,

                    "supp_name2" => $supp_name2,
                    "supp_ttl2" => $supp_ttl2,
                    "supp_dob2" => $supp_dob2,
                    "hub_supp2" => $hub_supp2,
                    "supp_mmn2" => $supp_mmn2,

                    "supp_name3" => $supp_name3,
                    "supp_ttl3" => $supp_ttl3,
                    "supp_dob3" => $supp_dob3,
                    "hub_supp3" => $hub_supp3,
                    "supp_mmn3" => $supp_mmn3,

                    "notes_1" => $notes_1,
                    "notes_2" => $notes_2,
                    "notes_3" => $notes_3,

                    "kode_pos" => $kode_pos,
                    "catatan_1" => $catatan_1,
                    "catatan_2" => $catatan_2,

                    "created" => date("Y-m-d H:i:s"),
                    "is_active" => 1
                );
                $this->db->insert("tb_prospect_pickup", $data);

                //update status
                $where = array(
                    "id_prospect" => $id_prospect
                );
                $data_toupdate = array(
                    "id_app_status" => "2"
                );
                $this->db->update("tb_prospect", $data_toupdate, $where);

                //var_dump($data_toupdate);
                //die("ddd");

                //calltrack
                $remark = "";
                $id_callcode = $parent_id;
                $notelp = $no_contacted;
                $begin_call_time = "";
                $idle_time = "";
                $end_call_time = "";
                $total_call_time = "";
                $date = date("Y-m-d H:i:s");

                $this->prospect_model->add_calltrack(
                    $id_prospect,
                    $notelp,
                    $this->id_user,
                    $id_callcode,
                    '',
                    date("Y-m-d"),
                    date("H:i:s"),
                    '',
                    $remark,
                    '',
                    '',
                    $begin_call_time,
                    $end_call_time,
                    $idle_time,
                    $total_call_time
                );
            } else {
                $sql3 = "select id_prospect from tb_delivery 
					where id_prospect='$id_prospect' and last_id_callcode='38' 
					and is_status in(3,5,6) ";
                $q3 = $this->db->query($sql3);
                if ($q3->num_rows() > 0) {
                    $kurir = $this->notelp_model->get_courier_name($region);
                    $barcode = "";
                    $j = strlen($id_prospect);
                    for ($i = 10; $j < $i; $j++) {
                        $barcode .= '0';
                    }
                    $barcode .= $id_prospect . date('Ymd');
                    $data = array(
                        "is_status"         => 1,
                        "barcode"             => $barcode,
                        "is_close"             => 1,
                        "closing_date"         => date('Y-m-d H:i:S'),
                        "date_update"         => date('Y-m-d H:i:S'),
                        "is_active"            => 0,
                        "region"            => $region,
                        "pickup_address"    => $remark,
                        "kode_pos"            => $kode_pos,
                        "catatan_1"            => $catatan_1,
                        "catatan_2"            => $catatan_2,
                        "courier_name"        => $kurir
                    );

                    $this->db->where('id_prospect', $id_prospect);
                    $this->db->update("tb_delivery", $data);

                    $sql = "insert into tb_calltrack_pod(id_prospect,is_status,status_pod,created,barcode) 
								select id_prospect,1,1,date(now()),barcode  
								from tb_delivery 
								where id_prospect in($id_prospect) ";
                    $this->db->query($sql);

                    //reset status
                    $where = array(
                        "id_prospect" => $id_prospect,
                        "id_user" => $this->id_user,
                    );
                    $data_toupdate = array(
                        "is_active" => "0"
                    );
                    $this->db->update("tb_prospect_pickup", $data_toupdate, $where);

                    //add new record
                    $data = array(
                        "id_prospect" => $id_prospect,
                        "id_user" => $this->id_user,
                        "remark" => $remark,
                        "supp_name1" => $supp_name1,
                        "supp_ttl1" => $supp_ttl1,
                        "supp_dob1" => $supp_dob1,
                        "hub_supp1" => $hub_supp1,
                        "supp_mmn1" => $supp_mmn1,

                        "supp_name2" => $supp_name2,
                        "supp_ttl2" => $supp_ttl2,
                        "supp_dob2" => $supp_dob2,
                        "hub_supp2" => $hub_supp2,
                        "supp_mmn2" => $supp_mmn2,

                        "supp_name3" => $supp_name3,
                        "supp_ttl3" => $supp_ttl3,
                        "supp_dob3" => $supp_dob3,
                        "hub_supp3" => $hub_supp3,
                        "supp_mmn3" => $supp_mmn3,

                        "notes_1" => $notes_1,
                        "notes_2" => $notes_2,
                        "notes_3" => $notes_3,

                        "kode_pos" => $kode_pos,
                        "catatan_1" => $catatan_1,
                        "catatan_2" => $catatan_2,

                        "created" => date("Y-m-d H:i:s"),
                        "is_active" => 1
                    );
                    $this->db->insert("tb_prospect_pickup", $data);

                    //update status
                    $where = array(
                        "id_prospect" => $id_prospect
                    );
                    $data_toupdate = array(
                        "id_app_status" => "2"
                    );
                    $this->db->update("tb_prospect", $data_toupdate, $where);

                    //var_dump($data_toupdate);
                    //die("ddd");

                    //calltrack
                    $remark = "";
                    $id_callcode = $parent_id;
                    $notelp = $no_contacted;
                    $begin_call_time = "";
                    $idle_time = "";
                    $end_call_time = "";
                    $total_call_time = "";
                    $date = date("Y-m-d H:i:s");

                    $this->prospect_model->add_calltrack(
                        $id_prospect,
                        $notelp,
                        $this->id_user,
                        $id_callcode,
                        '',
                        date("Y-m-d"),
                        date("H:i:s"),
                        '',
                        $remark,
                        '',
                        '',
                        $begin_call_time,
                        $end_call_time,
                        $idle_time,
                        $total_call_time
                    );
                }
            }
        }
        /*
			//reset status
			$where = array(
				"id_prospect" => $id_prospect,
				"id_user" => $this->id_user,
			);
			$data_toupdate = array(
				"is_active" => "0"
			);
			$this->db->update("tb_prospect_pickup", $data_toupdate, $where);

			//add new record
			$data = array(
				"id_prospect" => $id_prospect,
				"id_user" => $this->id_user,
				"remark" => $remark,
				"supp_name1" => $supp_name1,
				"supp_ttl1" => $supp_ttl1,
				"supp_dob1" => $supp_dob1,
				"hub_supp1" => $hub_supp1,
				"supp_mmn1" => $supp_mmn1,
				
				"supp_name2" => $supp_name2,
				"supp_ttl2" => $supp_ttl2,
				"supp_dob2" => $supp_dob2,
				"hub_supp2" => $hub_supp2,
				"supp_mmn2" => $supp_mmn2,
				
				"supp_name3" => $supp_name3,
				"supp_ttl3" => $supp_ttl3,
				"supp_dob3" => $supp_dob3,
				"hub_supp3" => $hub_supp3,
				"supp_mmn3" => $supp_mmn3,
				
				"notes_1" => $notes_1,
				"notes_2" => $notes_2,
				"notes_3" => $notes_3,
				
				"kode_pos" => $kode_pos,
				"catatan_1" => $catatan_1,
				"catatan_2" => $catatan_2,
				
				"created" => date("Y-m-d H:i:s"),
				"is_active" => 1
			);
			$this->db->insert("tb_prospect_pickup", $data);

			//update status
			$where = array(
				"id_prospect" => $id_prospect
			);
			$data_toupdate = array(
				"id_app_status" => "2"
			);
			$this->db->update("tb_prospect", $data_toupdate, $where);

			//var_dump($data_toupdate);
			//die("ddd");

			//calltrack
      $remark = "";
      $id_callcode = $parent_id;
      $notelp = $no_contacted;
      $begin_call_time = "";
      $idle_time = "";
      $end_call_time = "";
      $total_call_time = "";
      $date = date("Y-m-d H:i:s");

      $this->prospect_model->add_calltrack($id_prospect, $notelp, $this->id_user,
      	$id_callcode, '', date("Y-m-d"), date("H:i:s"), '', $remark, '', '', $begin_call_time,
        $end_call_time, $idle_time, $total_call_time);

		*/
        //die('pickup prospect');

        redirect('tsr/main');
    }

    public function submit_reminder()
    {

        $re_date = $this->input->post("re_date") ? $this->input->post("re_date") : '';
        $re_time = $this->input->post("re_time") ? $this->input->post("re_time") : '';
        $id_prospect = $this->input->post("id_prospect") ? $this->input->post("id_prospect") : '';

        $remark = $this->input->post("remark") ? $this->input->post("remark") : '';
        $last_id_callcode = $this->input->post("last_id_callcode") ? $this->input->post("last_id_callcode") : '';

        $this->reminder_model->add_reminder($this->id_user, $id_prospect, $re_date, $re_time, $remark, $last_id_callcode);

        //die("dorr" . $id_prospect);
        redirect('tsr/main');
    }


    public function close_reminder($id_reminder, $id_prospect)
    {
        $this->reminder_model->set_viewed($id_reminder, $id_prospect);
    }

    public function get_reminder()
    {
        $data["detail"] = $this->reminder_model->get_reminder_now($this->id_user);
        $data["detail"] = @$data["detail"][0];
        if (!empty($data["detail"])) {
            $this->reminder_model->set_viewed($data["detail"]["id_reminder"]);
            $data = $this->load->view('tsr/get_reminder', @$data);
        }
    }

    public function data_call_first($prospectArr)
    {
        $call_attempt = $this->input->post('call_attempt', TRUE);
        $data['username'] = strtoupper($this->input->post('username', TRUE));
        $data['id_user'] = strtoupper($this->input->post('id_user', TRUE));
        $data['id_prospect'] = $this->input->post('id_prospect', TRUE);
        $data['id_notelp'] = $this->input->post('id_notelp', TRUE);
        $data['id_callcode'] = $this->input->post('id_callcode');
        $data['id_calltrack'] = $this->input->post('id_calltrack') != "" ? $this->input->post('id_calltrack') : NULL;
        $data['no_contacted'] = $this->input->post('no_contacted', TRUE);
        $data['id_line_call'] = $this->input->post('id_line_call', TRUE);
        $data['id_product'] = $this->input->post('id_product', TRUE);
        $data['id_campaign'] = $this->input->post('id_campaign', TRUE);
        $data['call_date'] = date('Y-m-d');
        $data['call_time'] = date('H:i:s');
        $data['call_month'] = date('m');
        $data['call_attempt'] = $call_attempt;
        $data['sip_call'] = uniqid();

        ## Add Leader information
        $data['id_spv'] = $this->input->post('id_spv', TRUE);
        $data['id_tsm'] = $this->input->post('id_tsm', TRUE);

        ## Add Last Data Information
        $data['last_datespv'] = $prospectArr['date_spv'];
        $data['last_datetsr'] = $prospectArr['date_tsr'];
        $data['last_isrecycle'] = $prospectArr['is_recycle'];
        $data['last_recycledate'] = $prospectArr['last_recycle'];
        $data['last_idcallcode'] = $prospectArr['last_id_callcode'];

        //        ## Get TSM ID
        //        $this->db->select('id_leader');
        //        $this->db->where('id_user', $this->input->post('id_spv', TRUE));
        //        $qObj = $this->db->get('tb_users');
        //        
        //        $data['id_tsm'] = '0';
        //        if($qObj->num_rows() > 0):
        //        $qArr = $qObj->row_array();
        //            $data['id_tsm'] = $qArr['id_leader'];
        //        endif;

        return $data;
    }

    public function sip_call()
    {
        $this->load->helper('cyrusenyx');
        $this->load->model('misc/misc_model');

        $id_prospect = $this->input->post('id_prospect');

        ## Get Prospect Data
        $this->db->where('id_prospect', $id_prospect);
        $qObj = $this->db->get('tb_prospect');
        $qArr = $qObj->num_rows() > 0 ? $qObj->row_array() : array();
        $prospectArr = $qArr;
        unset($qObj, $qArr);

        $data = $this->data_call_first($prospectArr);

        ## Get Line
        $this->db->where('id_user', $_SESSION['id_user']);
        $this->db->join('tb_line', 'tb_users.id_line_call = tb_line.id_line_call', 'LEFT');
        $lObj = $this->db->get('tb_users');
        //echo $this->db->last_query(); die();
        $rArr = $lObj->row_array();

        ## Additional Data for Calltrack
        $data['id_line_call'] = $rArr['id_line_call'];
        $data['context'] = $rArr['context'];

        $no_contacted = $this->input->post('no_contacted');
        $id_product = $this->input->post('id_product');
        $id_calltrack = $this->input->post('id_calltrack');
        $is_routeback = $this->input->post('is_routeback');
        $id_campaign = $this->misc_model->get_tableDataById('tb_prospect', $id_prospect, 'id_prospect', 'id_campaign');
        $camptype = $this->misc_model->get_campaigndata($id_campaign, 'campaign_type');

        $id_parent = $this->input->post('id_parent');
        $data['remark'] = $this->input->post('remark');
        $call_attempt = $this->input->post('call_attempt', TRUE) + 1;

        ## Check is callblocking enabled
        $res = $this->config_model->get_list_setup('id_call_setup="10"');
        $callblock_enable = intval($res[0]['value']);

        $is_priority = intval($this->misc_model->get_tableDataById('tb_prospect', $id_prospect, 'id_prospect', 'is_priority'));
        $is_agree = intval($prospectArr['is_agree']);
        //var_dump($callblock_enable); die();
        if ($is_priority == 1 || $is_agree == 1) {
            $callblock_enable = 0; ## Overide to disable call blocking
        }

        ## update user activity
        $tb_users = array(
            'last_call' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_user', $_SESSION['id_user']);
        $this->db->update('tb_users', $tb_users);

        if ($id_calltrack != "") {
            echo 'data has been inserted';
            $insert_id = $this->notelp_model->insert_sip_call($data);
        } else {
            if (isset($id_prospect)) {
                $insert_id = $this->notelp_model->insert_sip_call($data);
                // $this->prospect_model->set_product($id_prospect,$id_product);
                //die($data['id_calltrack']);
                $this->db->select('fullname');
                $this->db->where('id_prospect', $id_prospect);
                $qObj = $this->db->get('tb_prospect');
                $qArr = $qObj->row_array();
                $fullname = $qArr['fullname'];
                $fullname = preg_replace('/[^a-zA-Z0-9\s]/', '', $fullname);

                $xurl = $rArr['ip_server'] . $rArr['value'] . '?dari=SIP/' . id_asterisk() . '&to=' . clean_call($no_contacted) . '&recid=' . $insert_id . '&cust_id=' . $id_prospect . '&fullname=' . $fullname . '&id_user=' . $_SESSION['id_user'] . '&camptype=' . $camptype . '&product=' . $id_product . '&id_line_call=' . $rArr['id_line_call'] . '&context=' . $rArr['context'];
                echo '<script  type="text/javascript">';
                echo "jQuery('#no_contacted').val('$no_contacted');";
                echo 'var call_attempt = ' . $call_attempt . ';';
                echo " jQuery('#id_calltrack').val(" . $insert_id . "); jQuery('#id_parent').val(" . $insert_id . ");  jQuery('#is_call').val(1);jQuery('#call_attempt').val(call_attempt);jQuery.unblockUI();";
                //echo "  sipCallblock(' " . ip_call() . "/" . id_asterisk() . "&to=" . clean_call($no_contacted) . "&recid=" . $insert_id . "&cust_id=" . $id_prospect . "&fullname=".$fullname. "&id_user=".$_SESSION['id_user']. "&camptype=".$camptype."&id_calltrack=".$insert_id."&product=".$id_product."', '" . $no_contacted . "', '" . $id_prospect . "', '" . $id_product . "', '" . $no_contacted . "')</script>";
                echo "  sipCallblock(' " . $xurl . "', '" . $no_contacted . "', '" . $id_prospect . "', '" . $id_product . "', '" . $no_contacted . "')</script>";
                echo '<script> jQuery.unblockUI();</script>';
                //echo 'Case has been updated!';
                //echo 'ip_call() . "/dial_number?dari=" . id_asterisk() . "&to=" . clean_call($no_contacted) . "&recid=" . $insert_id . "&cust_id=" . $id_prospect . "', '" . $no_contacted . "', '" . $id_prospect . "', '" . $id_product . "', '" . $no_contacted . "';
                //die();
            }
        }

        ## Create Call Session
        $this->load->model('callsession_model');
        $data['id_calltrack'] = $insert_id;
        if ($is_routeback != '1') { ## Don't Create Call Session on Routeback Data
            $this->callsession_model->create_callsession($data);
        }
    }

    function sip_call_v2()
    {
        //$data = $this->data_call_first();
        $id_prospect = $this->input->post('id_prospect');
        $no_contacted = $this->input->post('no_contacted');
        $id_product = $this->input->post('id_product');
        $id_calltrack = 0;
        $call_attempt = 1;
        //var_dump($id_prospect, $no_contacted, $id_product, $id_calltrack, $call_attempt);
        //die();
        if (isset($id_prospect)) {
            $insert_id = 0;

            $this->db->select('fullname');
            $this->db->where('id_prospect', $id_prospect);
            $qObj = $this->db->get('tb_prospect');
            $qArr = $qObj->row_array();
            $fullname = $qArr['fullname'];
            $fullname = preg_replace('/[^a-zA-Z0-9\s]/', '', $fullname);

            echo '<script  type="text/javascript">';
            echo " sipCallblock(' " . ip_call() . "/" . id_asterisk() . "&to=" . clean_call($no_contacted) . "&recid=" . $insert_id . "&cust_id=" . $id_prospect . "&fullname=" . $fullname . "&id_user=" . $_SESSION['id_user'] . "', '" . $no_contacted . "', '" . $id_prospect . "', '" . $id_product . "', '" . $no_contacted . "')</script>";
            //echo 'Case has been updated!';
            //echo 'ip_call() . "/dial_number?dari=" . id_asterisk() . "&to=" . clean_call($no_contacted) . "&recid=" . $insert_id . "&cust_id=" . $id_prospect . "', '" . $no_contacted . "', '" . $id_prospect . "', '" . $id_product . "', '" . $no_contacted . "';
            //die();
        }
    }

    public function set_noteligible()
    {
        $this->load->helper('cyrusenyx');
        $this->load->model('misc/misc_model');

        $id_prospect = $this->input->post('id_prospect');
        $id_campaign = $this->input->post('id_campaign');
        $now = date('Y-m-d H:i:s');

        ## Get Prospect Data
        $this->db->where('id_prospect', $id_prospect);
        $qObj = $this->db->get('tb_prospect');
        $qArr = $qObj->num_rows() > 0 ? $qObj->row_array() : array();
        $prospectArr = $qArr;
        unset($qObj, $qArr);

        $data = $this->data_call_first($prospectArr);
        $data['remark'] = '';
        $data['id_callcode'] = '82'; // Not Eligible
        if (isset($id_prospect)) {
            $insert_id = $this->notelp_model->insert_sip_call($data);

            ## Update Table Prospect ( last update )
            $update = array(
                'last_id_callcode' => $data['id_callcode'],
                'last_remark' => '',
                'last_calltime' => $now
            );
            $this->db->where('id_prospect', $id_prospect);
            $this->db->update('tb_prospect', $update);

            ## Clear Call Session
            $this->load->model('callsession_model');
            $this->callsession_model->clearCallsession();
        }
        echo site_url() . 'tsr/main/' . $id_campaign;
        exit;
    }

    public function get_option_callcode($parent_id = "", $id_prospect = "", $id_product = "", $id_calltrack, $no_contacted = "", $multioffers = '0', $multioffcard = "", $multiidx = "")
    {
        //die($multioffers);
        //contacted
        $id_user = $this->id_user;

        //      $sql = "select tp.*, tc.campaign_type, tc.max_tertanggung, tc.multiproduct, tc.name, db_type.db_type as type_name
        //              from tb_prospect tp, tb_campaign tc, tb_dbtype db_type
        //              where tp.id_prospect=$id_prospect
        //                and tp.id_campaign=tc.id_campaign and tc.db_type=db_type.idx ";
        $sql = "
        SELECT tp.*, tc.campaign_type, tc.max_tertanggung, tc.multiproduct, tc.name, db_type.db_type AS type_name
        FROM tb_prospect tp
        LEFT JOIN tb_campaign tc
        ON tp.id_campaign=tc.id_campaign
        LEFT JOIN tb_dbtype db_type
        ON tc.db_type=db_type.idx
        WHERE
        tp.id_prospect = '{$id_prospect}'
     ";
        //die($sql);
        $q_cek = $this->db->query($sql);

        $row1 = $q_cek->row_array();

        $data_campaign_type = $row1['campaign_type']; // campaign type
        $data_campaign_id = $row1['id_campaign']; // id_campaign for redirect
        $data_campaign_name = $row1['name'];

        ##Data OFFER
        /*$data_cif = $row1['cif_no'];
      $data_card = $row1['card_number_basic'];
      $sqlo = "select tp.*, toff.id_campaign, toff.cif_no, toff.xsell_cardxsell, toff.xsell_cardnumber from tb_prospect tp, tb_xsell toff
        where tp.id_prospect='$id_prospect'
        and toff.cif_no='$data_cif'
        and toff.xsell_cardnumber='$data_card'
        and tp.id_campaign = toff.id_campaign";
      //die($sqlo);
      $off_cek = $this->db->query($sqlo);
      $rowoff=$off_cek->row_array();*/
        //var_dump($rowoff);die();
        ## kurir
        $sql = "SELECT * FROM tb_kurir WHERE is_active = 1";
        $qObj = $this->db->query($sql);
        if ($qObj->num_rows() > 0) {
            $kurirArr = $qObj->result_array();
        } else {
            $kurirArr = array();
        }
        //var_dump($parent_id);
        //die();
        if ($parent_id == 50 && $id_product == 41) {
            $where_product = " AND (id_product LIKE '%$id_product%') ";
            $list = $this->callcode_model->get_list_callcode1(" parent_id_callcode='" . $parent_id . "' AND is_active = 1 $where_product");
        } else {
            $where_product = " AND (id_product = '0' OR id_product LIKE '%$id_product%') ";
            $list = $this->callcode_model->get_list_callcode1(" parent_id_callcode='" . $parent_id . "' AND is_active = 1 $where_product");
        }


        ## DEBUG
        //echo $this->db->last_query();die();
        //var_dump($list); die();

        ## For Multiproduct Filter MSC
        $sql = "select tp.*, tc.campaign_product, tc.campaign_type, tc.max_tertanggung, tc.multiproduct, tc.name
              from tb_prospect tp, tb_campaign tc
              where tp.id_prospect=$id_prospect
                and tp.id_campaign=tc.id_campaign";
        // die($sql);
        $q_cek2 = $this->db->query($sql);
        $row2 = $q_cek2->row_array();
        $data_campaign_type2  = $row2['campaign_type']; // campaign type
        $data_campaign_id2    = $row2['id_campaign']; // id_campaign for redirect
        $data_campaign_name2  = $row2['name'];

        if ($parent_id == 52) { //CALLTRACK AGREE
            if ($multioffers == '0') { ## Main Product
                if ($id_product == 31) { // AGREE CC
                    $this->load->model('tsr/cardproduct_model', 'cardproduct');
                    $merge['submit_url'] = site_url() . 'tsr/agree_v2/' . $id_prospect . '/' . $id_product;
                    $merge['cardsegment'] = $this->cardproduct->getlist_cardsegment();
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('CC');
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['id_product'] = $id_product;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['list_program'] = $this->get_listprogram();
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $this->load->view('tsr/agree_cc', $merge);
                } else if ($id_product == 32) {
                    $merge['submit1_url'] = site_url() . 'tsr_v2/agree_v3/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('PL');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['list_program'] = $this->get_listprogram();
                    $merge['list_travelagent'] = $this->get_listtravelagent();
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $this->load->view('tsr/agree_pl', $merge);
                } else if ($id_product == 39 || $multioffers == 'NTB') { // LiveSmart
                    $this->load->model('offer_model');
                    $this->load->model('tsr/cardproduct_model', 'cardproduct');
                    $merge['submit1_url'] = site_url() . 'tsr_v2/agree_ls/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardsegment'] = $this->cardproduct->getlist_cardsegment_ntbnew();
                    // $merge['cardsegment'] = $this->cardproduct->getlist_cardsegment_ntb();
                    $merge['productcode'] = $this->product_model->get_productcode('NTB');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['bidang_usaha'] = $this->product_model->bidang_usaha();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multiproducts'] = $multioffers;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_ls', $merge);
                } else if ($id_product == 49 || $multioffers == 'CASA') { // CASA
                    $this->load->model('tsr/cardproduct_model', 'cardproduct');
                    $merge['submit1_url'] = site_url() . 'tsr/agree_casa/' . $id_prospect . '/' . $id_product;
                    //$merge['submit1_url'] = site_url().'agree_49/submit_agree/'.$id_prospect.'/'.$id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['cardsegment'] = $this->cardproduct->getlist_cardsegment_ntb();
                    $merge['productcode'] = $this->product_model->get_productcode('CASA');
                    $merge['list_branchcode'] = $this->product_model->getlist_branchcode();
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    //$merge['list_program'] = $this->get_listprogram();
                    //$merge['list_travelagent'] = $this->get_listtravelagent();
                    $merge['insert_id'] = "";
                    $merge['multiproducts'] = $multioffers;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_nt', $merge);
                } else if ($id_product == 40 || $multioffers == 'CRP') { // Covid19 Relief Program
                    $this->load->model('transactioncrp_model');
                    $merge['submit1_url'] = site_url() . 'agree_40/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('CRP', "status='{$row1['status']}'");
                    //echo $this->db->last_query(); die();
                    /* $merge['transactions'] = $this->transaction_model->get_transactions($merge['prospect_detail']['cnum'],$data_campaign_id); */

                    $merge['transactions'] = $this->transactioncrp_model->get_transactions_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['transactions1'] = $this->transactioncrp_model->get_transactions_byCardNumb($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);

                    $merge['counttransactions'] = $this->transactioncrp_model->get_transactions_byCountCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    //var_dump($merge['counttransactions']['']);
                    //$merge['transactions1'] = $this->transactioncrp_model->get_transactions_byCardNum1($merge['prospect_detail']['cif_no'],$data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    //$merge['transactions2'] = $this->transactioncrp_model->get_transactions_byCardNum2($merge['prospect_detail']['cif_no'],$data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    //var_dump($merge['transactions1']);

                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_crp', $merge);
                } else if ($id_product == 41 || $multioffers == 'xTMRW') { // xTMRW
                    $this->load->model('tsr/cardproduct_model', 'cardproduct');
                    $merge['submit1_url'] = site_url() . 'tsr/agree_xtmrw/' . $id_prospect . '/' . $id_product;
                    //$merge['submit1_url'] = site_url().'agree_49/submit_agree/'.$id_prospect.'/'.$id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['cardsegment'] = $this->cardproduct->getlist_cardsegment_ntb();
                    $merge['productcode'] = $this->product_model->get_productcode('xTMRW');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    //$merge['list_program'] = $this->get_listprogram();
                    //$merge['list_travelagent'] = $this->get_listtravelagent();
                    $merge['insert_id'] = "";
                    $merge['multiproducts'] = $multioffers;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_xtmrw', $merge);
                } else if ($id_product == 42 || $multioffers == 'STASH') { // Combo 3
                    $this->load->model('tsr/cardproduct_model', 'cardproduct');
                    $merge['submit1_url'] = site_url() . 'tsr/agree_stash/' . $id_prospect . '/' . $id_product;
                    //$merge['submit1_url'] = site_url().'agree_49/submit_agree/'.$id_prospect.'/'.$id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['cardsegment'] = $this->cardproduct->getlist_cardsegment_ntb();
                    $merge['productcode'] = $this->product_model->get_productcode('STASH');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    //$merge['list_program'] = $this->get_listprogram();
                    //$merge['list_travelagent'] = $this->get_listtravelagent();
                    $merge['insert_id'] = "";
                    $merge['multiproducts'] = $multioffers;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_stash', $merge);
                } else if ($id_product == 43 || $multioffers == 'PC4') { // Combo 4
                    $merge['submit1_url'] = site_url() . 'tsr/agree_pc4/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('PC4');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_pc4', $merge);
                } else if ($id_product == 44 || $multioffers == 'FP') { // FlexiPay
                    $this->load->model('transactionFp_model');
                    $merge['submit1_url'] = site_url() . 'agree_44/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('FP', "status='{$row2['status']}'");
                    $merge['transactions'] = $this->transactionFp_model->get_transactions($merge['prospect_detail']['cif_no'], $data_campaign_id2, $id_prospect, $merge['prospect_detail']['uploadcode']);
                    $merge['cardtype'] = $this->transactionFp_model->get_cardtype($merge['prospect_detail']['cif_no'], $data_campaign_id2);

                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row2['multiproduct'];
                    $merge['max_tertanggung'] = $row2['max_tertanggung'];
                    $this->load->view('tsr/agree_fp', $merge);
                } else if ($id_product == 46 || $multioffers == 'COP') { // Cash On Phone
                    $merge['submit1_url'] = site_url() . 'agree_46/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);

                    if (strpos($data_campaign_name, 'NTK') > 0) {
                        $merge['productcode'] = $this->product_model->get_productcode('COP', "(status='{$row1['status']}' OR status='TYPE 28') AND `segment` NOT LIKE 'COP TMRW%'");
                    } else if (strpos($data_campaign_name, 'DAP') > 0) {
                        $merge['productcode'] = $this->product_model->get_productcode('COP', "(status='DAP')");
                    } else if (strpos($data_campaign_name, 'TMRW') > 0) {
                        $merge['productcode'] = $this->product_model->get_productcode('COP', "(status='{$row1['status']}' AND `segment` LIKE 'COP TMRW%')");
                    } else if (strpos($data_campaign_name, 'COP') > 0) {
                        // onmra:
                        $merge['productcode'] = $this->product_model->get_productcode('COP', "(status='{$row1['status2']}')  AND `segment` NOT LIKE 'COP TMRW%'" . ($row1['custom1'] ? " OR (fg_can_free_rate = 'Y' AND msc = 'COP $row1[custom1]')" : ''));
                    } else {
                        $merge['productcode'] = $this->product_model->get_productcode('COP', "(status='{$row1['status']}' AND segment!='{$row1['status']}') AND `segment` NOT LIKE 'COP TMRW%'" . ($row1['custom1'] ? " OR (fg_can_free_rate = 'Y' AND msc = 'COP $row1[custom1]')" : ''));
                    }
                    //                    else { $merge['productcode'] = $this->product_model->get_productcode('COP', "(status='{$row1['status']}' AND segment!='{$row1['status']}') AND `segment` NOT LIKE 'COP TMRW%'"); }
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $this->load->model('cop_model');
                    $merge['refreshbag'] = $this->cop_model->get_datarefresh($row1['cif_no'], $row1['card_number_basic'], $row1['refreshcode']);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $merge['cop_flexibleamount'] = $this->config_model->get_list_setup('id_call_setup="19"');
                    $this->load->view('tsr/agree_cop', $merge);
                } else if ($id_product == 47 || $multioffers == 'SUP') { // Supplement

                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    //$id_prospect = $this->uri->segment(4);
                    $merge['submit1_url'] = site_url() . 'agree_47/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('SUP');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['max_tertanggung'] = 4;
                    //var_dump($merge['multioffcard']);die();
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_supp', $merge);
                } else if ($id_product == 48 || $multioffers == 'ACS') { // AKTIVASI SUPPLEMENT

                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('offer_model');
                    $this->load->model('activasi_model');
                    //$id_prospect = $this->uri->segment(4);
                    $merge['submit1_url'] = site_url() . 'agree_48/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardnumber'] = $this->activasi_model->get_activasi_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('ACS');
                    $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_atv', $merge);
                } else if ($id_product == 50 || $multioffers == 'ACT') { // AKTIVASI BASIC
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('offer_model');
                    $this->load->model('activasi_model');
                    //$id_prospect = $this->uri->segment(4);
                    $merge['submit1_url'] = site_url() . 'agree_48/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardnumber'] = $this->activasi_model->get_activasi_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('ACT');
                    $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_atv', $merge);
                } else if ($id_product == 51 || $multioffers == 'UPG') { // Upgrade
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('update_model');
                    $merge['submit1_url'] = site_url() . 'agree_51/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['cardnumber'] = $this->update_model->get_update_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('UPG');
                    $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_upgrade', $merge);
                } else if ($id_product == 52 || $multioffers == 'CPM') { // Upgrade
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('cp_model');
                    $merge['submit1_url'] = site_url() . 'agree_52/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['cardnumber'] = $this->cp_model->get_cp_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('CPM');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_cp', $merge);
                } else if ($id_product == 53 || $multioffers == 'CPR') { // Upgrade
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('cp_model');
                    $merge['submit1_url'] = site_url() . 'agree_52/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['cardnumber'] = $this->cp_model->get_cp_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('CPR');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_cp', $merge);
                } else if ($id_product == 54 || $multioffers == 'RAC') { //$id_product == 54 ){ // RAC
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('update_model');
                    $this->load->model('offer_model');
                    $this->load->model('rac_model');
                    $merge['submit1_url'] = site_url() . 'agree_54/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    // $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'],$data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardnumber'] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('RAC');
                    // echo $this->db->last_query();
                    // $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'],$data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge["detRac"] = $this->rac_model->get_det_rac($merge['prospect_detail']['cif_no'], $merge['prospect_detail']['id_campaign']);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['multiidx'] = $multiidx;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_rac', $merge);
                } else if ($id_product == 56 || $multioffers == 'CPILX') { // LiveSmart
                    $this->load->model('offer_model');
                    $this->load->model('tsr/cardproduct_model', 'cardproduct');
                    $merge['submit1_url'] = site_url() . 'agree_59/submit_agree/' . $id_prospect . '/' . $id_product;
                    // $merge['submit1_url'] = site_url() . 'tsr/agree_cpx/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    //$merge['cardsegment'] = $this->cardproduct->getlist_cardsegment_ntb();
                    $merge['productcode'] = $this->product_model->get_productcode('CPILX', "status='{$row2['status']}'");
                    //$merge['productcode'] = $this->product_model->get_productcode('CPX');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multiproducts'] = $multioffers;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_cpx', $merge);
                } else if ($id_product == 55 || $multioffers == 'CPIL') { // Cash On Phone
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    //$id_prospect = $this->uri->segment(4);
                    $merge['submit1_url'] = site_url() . 'agree_57/submit_agree/' . $id_prospect . '/' . $id_product;
                    $this->load->model('cop_model');
                    $merge['refreshbag'] = $this->cop_model->get_datarefreshcpil($row1['cif_no'], $row1['card_number_basic'], $row1['refreshcode']);
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    // $merge['productcode'] = $this->product_model->get_productcode('CPIL', "status='{$row2['status']}'");
                    $merge['productcode'] = $this->product_model->get_productcode('CPIL', "status='{$row2['status']}'" . ($row2['custom1'] ? " OR (fg_can_free_rate = 'Y' AND msc = 'CPIL $row2[custom1]')" : ''));
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $merge['cop_flexibleamount'] = $this->config_model->get_list_setup('id_call_setup="19"');
                    $this->load->view('tsr/agree_cashplus', $merge);
                } else if ($id_product == 57 || $multioffers == 'FOPST') { // FlexiPay
                    $this->load->model('transactionFp_model');
                    $merge['submit1_url'] = site_url() . 'agree_60/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('FP', "status='{$row2['status']}'");
                    $merge['transactions'] = $this->transactionFp_model->get_transactions($merge['prospect_detail']['cif_no'], $data_campaign_id2, $id_prospect, $merge['prospect_detail']['uploadcode']);
                    $merge['transactionscard'] = $this->transactionFp_model->get_transactionscard($merge['prospect_detail']['cif_no'], $data_campaign_id2, $id_prospect, $merge['prospect_detail']['uploadcode']);
                    $merge['cardtype'] = $this->transactionFp_model->get_cardtype($merge['prospect_detail']['cif_no'], $data_campaign_id2);

                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row2['multiproduct'];
                    $merge['max_tertanggung'] = $row2['max_tertanggung'];
                    $this->load->view('tsr/agree_fpst', $merge);
                } else if ($id_product == 58 || $multioffers == 'FOPUN') { // FlexiPay
                    $this->load->model('transactionFp_model');
                    $this->load->model('offer_model');
                    $merge['submit1_url'] = site_url() . 'agree_62/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('FP', "status='{$row2['status']}'");
                    $merge['transactions'] = $this->transactionFp_model->get_transactionsun($merge['prospect_detail']['cif_no'], $data_campaign_id2, $id_prospect, $merge['prospect_detail']['uploadcode']);
                    // $merge['transactions'] = $this->transactionFp_model->get_transactionsunbilled($merge['prospect_detail']['cif_no'], $data_campaign_id2, $id_prospect, $merge['prospect_detail']['uploadcode']);
                    $merge['cardtype'] = $this->transactionFp_model->get_cardtype($merge['prospect_detail']['cif_no'], $data_campaign_id2);
                    // $merge["trxidx"] = $this->offer_model->get_offertrx_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id2, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row2['multiproduct'];
                    $merge['max_tertanggung'] = $row2['max_tertanggung'];
                    $this->load->view('tsr/agree_fopun', $merge);
                } else {
                    die('ERR: Invalid Product');
                }
            } else { ## Multiproduct
                if ($multioffers == 'CC') { // AGREE CC
                    $this->load->model('tsr/cardproduct_model', 'cardproduct');
                    $merge['submit_url'] = site_url() . 'tsr/agree_v2/' . $id_prospect . '/' . $id_product;
                    $merge['cardsegment'] = $this->cardproduct->getlist_cardsegment();
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('CC');
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['id_product'] = $id_product;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['list_program'] = $this->get_listprogram();
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $this->load->view('tsr/agree_cc', $merge);
                } else if ($multioffers == 'PL') { //AGREE PL
                    $merge['submit1_url'] = site_url() . 'tsr_v2/agree_v3/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('PL');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['list_program'] = $this->get_listprogram();
                    $merge['list_travelagent'] = $this->get_listtravelagent();
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $this->load->view('tsr/agree_pl', $merge);
                } else if ($multioffers == 'CPR') { //AGREE CP
                    $this->load->model('billpayment_model');
                    $this->load->model('misc/misc_model');
                    $this->load->model('cp_model');
                    $this->load->model('offer_model');
                    $merge['submit1_url'] = site_url() . 'agree_52/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardnumber'] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('CPR');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['multiidx'] = $multiidx;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $this->load->view('tsr/agree_cp', $merge);
                } else if ($multioffers == 'CPM') { //AGREE CP
                    $this->load->model('billpayment_model');
                    $this->load->model('misc/misc_model');
                    $this->load->model('cp_model');
                    $this->load->model('offer_model');
                    $merge['submit1_url'] = site_url() . 'agree_52/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardnumber'] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('CPM');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['multiidx'] = $multiidx;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $this->load->view('tsr/agree_cp', $merge);
                } else if ($multioffers == 'BP') { //AGREE BP
                    $this->load->model('billpayment_model');
                    $this->load->model('misc/misc_model');
                    $dummy_id = $this->misc_model->get_tableDataById('tb_prospect', $id_prospect, 'id_prospect', 'dummy_id');
                    $merge['submit1_url'] = site_url() . 'tsr/agree_bp/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('BP');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_providers'] = $this->billpayment_model->get_providers('is_active = 1');
                    $merge['list_cards'] = $this->billpayment_model->get_cust_cards($dummy_id, 'is_active = 1');
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $this->load->view('tsr/agree_bp', $merge);
                } else if ($id_product == 37 || $multioffers == 'PJ') { //AGREE PJ
                    $this->load->model('billpayment_model');
                    $this->load->model('misc/misc_model');
                    $dummy_id = $this->misc_model->get_tableDataById('tb_prospect', $id_prospect, 'id_prospect', 'dummy_id');
                    $merge['submit1_url'] = site_url() . 'tsr/agree_pj/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('PJ');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $merge['list_cards'] = $this->billpayment_model->get_cust_cards($dummy_id, 'is_active = 1');
                    $this->load->view('tsr/agree_pj', $merge);
                } else if ($multioffers == 'PC3') {
                    $merge['submit1_url'] = site_url() . 'tsr/agree_pc3/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('PC3');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multiproducts'] = $multioffers;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_pc3', $merge);
                } else if ($multioffers == 'PC4') {
                    $merge['submit1_url'] = site_url() . 'tsr/agree_pc4/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('PC4');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multiproducts'] = $multioffers;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_pc4', $merge);
                } else if ($multioffers == 'SUP') {
                    $this->load->model('misc/misc_model');
                    $this->load->model('offer_model');
                    $merge['miscModel'] = $this->misc_model;
                    //$id_prospect = $this->uri->segment(4);
                    $merge['submit1_url'] = site_url() . 'agree_47/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['productcode'] = $this->product_model->get_productcode('SUP');
                    $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['multiidx'] = $multiidx;
                    $merge['max_tertanggung'] = 4;
                    $this->load->view('tsr/agree_supp', $merge);
                } else if ($multioffers == 'ACS') {
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('offer_model');
                    $this->load->model('activasi_model');
                    //$id_prospect = $this->uri->segment(4);
                    $merge['submit1_url'] = site_url() . 'agree_48/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardnumber'] = $this->activasi_model->get_activasi_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    //var_dump($merge["cardnumber"]);die();
                    $merge['productcode'] = $this->product_model->get_productcode('ACS');
                    $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['multiidx'] = $multiidx;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_atv', $merge);
                } else if ($multioffers == 'ACT') {
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('offer_model');
                    $this->load->model('activasi_model');
                    $merge['submit1_url'] = site_url() . 'agree_48/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardnumber'] = $this->activasi_model->get_activasi_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    //var_dump($merge["cardnumber"]);die();
                    $merge['productcode'] = $this->product_model->get_productcode('ACT');
                    $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['multiidx'] = $multiidx;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_atv', $merge);
                } else if ($id_product == 56 || $multioffers == 'CPILX') { // LiveSmart
                    $this->load->model('offer_model');
                    $this->load->model('tsr/cardproduct_model', 'cardproduct');
                    $merge['submit1_url'] = site_url() . 'agree_59/submit_agree/' . $id_prospect . '/' . $id_product;
                    // $merge['submit1_url'] = site_url() . 'tsr/agree_cpx/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    //$merge['cardsegment'] = $this->cardproduct->getlist_cardsegment_ntb();
                    $merge['productcode'] = $this->product_model->get_productcode('CPILX', "status='{$row2['status']}'");
                    //$merge['productcode'] = $this->product_model->get_productcode('CPX');
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge['list_paymentsource'] = $this->product_model->get_paymentsource('is_active = 1');
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multiproducts'] = $multioffers;
                    $merge['multiproduct'] = $row1['multiproduct'];
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_cpx', $merge);
                } else if ($multioffers == 'UPG') {
                    $this->load->model('misc/misc_model');
                    $merge['miscModel'] = $this->misc_model;
                    $this->load->model('update_model');
                    $this->load->model('offer_model');
                    $merge['submit1_url'] = site_url() . 'agree_51/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['list_bank'] = $this->product_model->get_bankname();
                    $merge["offers"] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['cardnumber'] = $this->offer_model->get_offer_byCardNum($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('UPG');
                    $merge["xsellidx"] = $this->offer_model->get_offer_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['list_kurir'] = $kurirArr;
                    $merge['insert_id'] = "";
                    $merge['multioffers'] = $multioffers;
                    $merge['multioffcard'] = $multioffcard;
                    $merge['multiidx'] = $multiidx;
                    $merge['max_tertanggung'] = $row1['max_tertanggung'];
                    $this->load->view('tsr/agree_upgrade', $merge);
                } else if ($id_product == 57 || $multioffers == 'FOPST') { // FlexiPay
                    $this->load->model('transactionFp_model');
                    $merge['submit1_url'] = site_url() . 'agree_60/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('FP', "status='{$row2['status']}'");
                    $merge['transactions'] = $this->transactionFp_model->get_transactions($merge['prospect_detail']['cif_no'], $data_campaign_id2, $id_prospect, $merge['prospect_detail']['uploadcode']);
                    $merge['transactionscard'] = $this->transactionFp_model->get_transactionscard($merge['prospect_detail']['cif_no'], $data_campaign_id2, $id_prospect, $merge['prospect_detail']['uploadcode']);
                    $merge['cardtype'] = $this->transactionFp_model->get_cardtype($merge['prospect_detail']['cif_no'], $data_campaign_id2);

                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row2['multiproduct'];
                    $merge['max_tertanggung'] = $row2['max_tertanggung'];
                    $this->load->view('tsr/agree_fpst', $merge);
                } else if ($id_product == 58 || $multioffers == 'FOPUN') { // FlexiPay
                    $this->load->model('transactionFp_model');
                    $this->load->model('offer_model');
                    $merge['submit1_url'] = site_url() . 'agree_62/submit_agree/' . $id_prospect . '/' . $id_product;
                    $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
                    $merge['productcode'] = $this->product_model->get_productcode('FP', "status='{$row2['status']}'");
                    $merge['transactions'] = $this->transactionFp_model->get_transactionsun($merge['prospect_detail']['cif_no'], $data_campaign_id2, $id_prospect, $merge['prospect_detail']['uploadcode']);
                    $merge['cardtype'] = $this->transactionFp_model->get_cardtype($merge['prospect_detail']['cif_no'], $data_campaign_id2);
                    // $merge["trxidx"] = $this->offer_model->get_offertrx_byidx($merge['prospect_detail']['cif_no'], $data_campaign_id2, $merge['prospect_detail']['card_number_basic'], $id_prospect);
                    $merge['id_product'] = $id_product;
                    $merge['no_contacted'] = $no_contacted;
                    $merge['id_calltrack'] = $id_calltrack;
                    $merge['insert_id'] = "";
                    $merge['multiproduct'] = $row2['multiproduct'];
                    $merge['max_tertanggung'] = $row2['max_tertanggung'];
                    $this->load->view('tsr/agree_fopun', $merge);
                } else {
                    die('ERR: Invalid Product');
                }
            } ## End Multi Product
        } else {
            $merge['base_url'] = base_url();
            $merge['parent_id'] = $parent_id;
            $merge['id_prospect'] = $id_prospect;
            $merge['id_calltrack'] = $id_calltrack;
            $merge['id_product'] = $id_product;
            $merge['id_campaign'] = $data_campaign_id;
            $merge['list'] = $list;
            $this->load->view('tsr/callcode_option', $merge);
        }
    }

    function load_formPL($id_prospect = "", $id_product = "", $id_calltrack, $no_contacted = "", $insert_id = "")
    {
        //=> id_prospect = no case untuk diupdate 
        //=> id_product untuk membedakan bundling
        //=> id_calltrack untuk update memo calltrack
        //=> insert_id untuk ambil value di tb_prospect_print untuk di parse;

        ## Kurir
        $sql = "SELECT * FROM tb_kurir WHERE is_active = 1";
        $qObj = $this->db->query($sql);
        if ($qObj->num_rows() > 0) {
            $kurirArr = $qObj->result_array();
        } else {
            $kurirArr = array();
        }

        $merge['submit1_url'] = site_url() . 'tsr_v2/agree_v3/' . $id_prospect . '/' . $id_product . '/BUNDLING/';
        $merge['parsedata_url'] = site_url() . 'ajax/tsr/getdata_prospectprint/' . $insert_id;

        $merge['prospect_detail'] = $this->prospect_model->get_prospectdetail($id_prospect);
        $merge['productcode'] = $this->product_model->get_productcode('PL');
        $merge['id_product'] = $id_product;
        $merge['no_contacted'] = $no_contacted;
        $merge['id_calltrack'] = $id_calltrack;
        $merge['list_kurir'] = $kurirArr;
        $merge['list_program'] = $this->get_listprogram();
        $merge['insert_id'] = $insert_id;
        $this->load->view('tsr/agree_pl', $merge);
    }

    public function form_reminder($id_callcode = '', $id_prospect = '')
    {
        $data['id_callcode'] = $id_callcode;
        $data['id_prospect'] = $id_prospect;
        echo $this->load->view('tsr/reminder', $data, true);
    }

    public function faq()
    {
        $this->load->model('tsr/faq_model', 'faq_model', true);

        $data["list"] = $this->faq_model->get_list_faq();

        $this->load->view('tsr/faq', $data);
    }

    public function notelp()
    {
        $id_prospect = $this->uri->segment(3);
        $data["list"] = $this->notelp_model->get_list_notelp($id_prospect);
        $data["prospect"] = $this->prospect_model->get_list_prospect("id_prospect='$id_prospect'");
        $data["id_prospect"] = $id_prospect;
        $data['ip_pabx'] = $this->ip_pabx;
        $data['local_number'] = $this->local_number;

        $this->load->view('tsr/notelp', $data);
    }

    public function submit_notelp()
    {
        $id_prospect = $this->uri->segment(3);
        $post = @$_POST["post"];

        if ($post) {
            //var_dump($_POST);
            //die();

            $no_telp = @$_POST["notelp"];
            $phone_type = @$_POST["phone_type"];
            //die($phone_type);
            $remark = @$_POST["remark"];

            $ip_pabx = $this->ip_pabx;
            $local_number = $this->local_number;

            $call_phone = substr($no_telp, 0, strlen($local_number)) != $local_number ? $no_telp : substr($no_telp, strlen($local_number));

            $prospect = $this->prospect_model->get_list_prospect("id_prospect='$id_prospect'");

            //update ke prospect
            switch ($phone_type) {
                case "home_phone1":
                    $sql = "update tb_prospect set home_phone1='$no_telp' where id_prospect=$id_prospect";
                    $this->db->query($sql);
                    break;
                case "home_phone2":
                    $sql = "update tb_prospect set home_phone2='$no_telp' where id_prospect=$id_prospect";
                    $this->db->query($sql);
                    break;
                case "office_phone1":
                    $sql = "update tb_prospect set office_phone1='$no_telp' where id_prospect=$id_prospect";
                    $this->db->query($sql);
                    break;
                case "office_phone2":
                    $sql = "update tb_prospect set office_phone2='$no_telp' where id_prospect=$id_prospect";
                    $this->db->query($sql);
                    break;
                case "hp1":
                    $sql = "update tb_prospect set hp1='$no_telp' where id_prospect=$id_prospect";
                    $this->db->query($sql);
                    break;
                case "hp2":
                    $sql = "update tb_prospect set hp2='$no_telp' where id_prospect=$id_prospect";
                    $this->db->query($sql);
                    break;
            }


            $call_script = "optionCodeCallTrack('" . site_url() . "/tsr/get_option_callcode/0/$id_prospect','connect');" .
                "call('" . site_url() . "/tsr/call/$call_phone/$id_prospect','$ip_pabx','SIP/" . $_SESSION['id_asterisk'] . "','$call_phone','$id_prospect','" . $prospect[0]['fullname'] . "');" .
                "hidePopUp()";
            //echo $call_script;die();

            if ($this->notelp_model->add_notelp($id_prospect, $this->id_user, $remark, $no_telp)) {
                echo "<tr><td>" . set_string_content($remark) . "</td><td>" . set_string_content($no_telp) . "</td><td></td></tr>";
            }
        }
    }

    public function close_prospect()
    {
        $id_prospect = $this->uri->segment(3);
        $this->prospect_model->close_prospect($id_prospect);
        redirect('tsr');
    }

    public function agree_referral_promo($id_prospect, $id_product = '')
    {
        $data["txt_title"] = "Agree " . $id_prospect;
        $post = @$_POST['post'];

        //var_dump($_POST);
        //die('');

        // save detail product
        if ($post) {



            $post = array();


            foreach ($_POST as $key => $value)
                $post[$key] = $this->input->xss_clean($value);

            //var_dump($_POST);

            $arrdata = $post;
            $arrdata['id_user'] = $this->id_user;
            $arrdata['id_prospect'] = $id_prospect;
            $arrdata['created_date'] = date("Y-m-d") . ' ' . date("H:i:s");
            //$pengiriman = $arrdata['pengiriman'];
            //$nama = $arrdata['nama'];

            //echo $pengiriman;

            //var_dump($arrdata);

            $id_user = $arrdata['id_user'];
            //die();





            //$faxno = '9'.$arrdata['fax_number'].'#456798';						
            //$faxno = $arrdata['fax_number'];
            //$mailto = $arrdata['mailto'];
            $jenis_aplikasi = $arrdata['is_fax'];

            // get fullname
            $sql3 = "select * from tb_prospect where id_prospect = '$id_prospect'";
            //echo $sql3;
            $q3 = $this->db->query($sql3);
            $data1 = $q3->row_array();

            $fullname = $data1['fullname'];
            $id_spv = $data1['id_spv'];


            //$this->prospect_model->get_fullname($id_prospect);



            //prospect print


            //echo 'fax_numberrrrrrr'$faxno;



            //

            $this->prospect_model->add_prospect_print($arrdata);
            $this->prospect_model->add_calltrack($id_prospect, '', $this->id_user, 36, '', date("Y-m-d"), date("H:i:s"), '', '', '', '', '', '', '', '');
            $this->prospect_model->set_is_agree($id_prospect, $id_product);
            //insert winfax



            $fax_number = $arrdata['fax_number'];
            $mailto = $arrdata['mailto'];

            if (TRIM($fax_number) != '') {
                $this->prospect_model->tb_winfax($id_prospect, $jenis_aplikasi, $fullname, $fax_number, $id_user, $id_spv);
            }

            if (TRIM($mailto) != '') {
                //insert email            
                $this->prospect_model->tb_email($id_prospect, $jenis_aplikasi, $fullname, $mailto, $id_user, $id_spv);
            }

            /*
            if($pengiriman==1){
							//echo "masuk pengiriman 1";
							//echo $nama;
							$this->prospect_model->tb_winfax($id_prospect,$jenis_aplikasi,$fullname,$nama,$id_user);
						}
						
						else{
							//echo "masuk pengiriman 2";
							$this->prospect_model->tb_email($id_prospect,$jenis_aplikasi,$fullname,$nama,$id_user);
						}
            */





            //die("dorr");

            //redirect('tsr/main');
            // yg dipake di agree referral redirect("tsr/main_print/$id_prospect");
            /*
           //cek cttype kalo 3 berarti incoming
           if()
           {
           }
 */

            //$sql=$this->db->query("select *, a.id_prospect, a.fullname from tb_campaign as b inner join tb_prospect as a on a.id_campaign = b.id_campaign");
            $sql = $this->db->query("select a.id_prospect, b.campaign_type, a.fullname from tb_campaign as b
            											inner join tb_prospect as a on a.id_campaign = b.id_campaign 
            											where a.id_prospect=$id_prospect");
            $row = $sql->row_array();
            $data = $row["campaign_type"];
            //var_dump($data);
            //die();
            if ($data == 3) {
                $record_close->id_app_status = 4;
                $this->db->update($this->tb_prospect, $record_close, array('id_prospect' => $id_prospect));
                //var_dump($this->db->last_query());
                //die();
                // echo("dididi");
                redirect("tsr/main_print/$id_prospect");
            } else {
                //die("dododo");
                redirect("tsr/main_print/$id_prospect");
            }

            //var_dump($this->db->last_query());
            //die();
        } else {
            $data["id_product"] = $id_product;

            //get prospect
            $q = $this->db->query("select * from tb_prospect where id_prospect=$id_prospect");
            $row_prospect = $q->row_array();
            $data["row_prospect"] = $row_prospect;
        }

        //script tele
        $sql = "select * from tb_product where published=1";
        $q = $this->db->query($sql);

        $row_script = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;

        //script tele 2	

        $sql1 = "select * from tb_script group by jenis_dokumen";

        $q1 = $this->db->query($sql1);



        $row_script1 = array();
        if ($q1->num_rows() > 0) {
            foreach ($q1->result_array() as $row1) {
                $row_script1[] = $row1;
            }
        }

        $data['scripts1'] = $row_script1;

        //script tele 2	


        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/agree_referral', $data);
        $this->load->view('tsr/footer', $data);
    }



    public function get_option_callcode_2($parent_id = "", $id_prospect, $no_contacted = "")
    {

        $data['txt_title'] = 'INCOMING';

        $data['id_prospect'] = $id_prospect;

        //echo $id_prospect;         	



        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/main_2', $data);
        $this->load->view('tsr/footer', $data);
    }

    //public function pickup_prospect_1($id_prospect)
    public function pickup_prospect_1($id_prospect, $card_type1 = "", $card_type2 = "")
    {

        //die($id_prospect);

        //$catatan_2 = $_POST['catatan_2'] ? $_POST['catatan_2'] : '';

        $post = @$_POST['post'];

        if ($post) {


            $post = array();


            foreach ($_POST as $key => $value)
                $post[$key] = $this->input->xss_clean($value);

            //var_dump($_POST);

            $arrdata1 = $post;
            $arrdata1['id_prospect'] = $id_prospect;
            $arrdata1['id_user'] = $this->id_user;


            //var_dump ($arrdata);


        }


        $card_type1 = @$_POST['card_type1'] ? @$_POST['card_type1'] : '';
        $card_type2 = @$_POST['card_type2'] ? @$_POST['card_type2'] : '';




        //$this->prospect_model->add_prospect_print_1($arrdata1);
        $this->prospect_model->add_prospect_print_1($arrdata1, $card_type1, $card_type2);
        $this->prospect_model->add_calltrack($id_prospect, '', $this->id_user, 42, '', date("Y-m-d"), date("H:i:s"), '', '', '', '', '', '', '', '');
        $this->prospect_model->set_is_agree($id_prospect, $id_product = "12");


        //$id_prospect = $_POST['id_prospect'] ? $_POST['id_prospect'] : '';    
        //$no_contacted = $_POST['contacted'] ? $_POST['contacted'] : '';
        $remark = $_POST['remark'] ? $_POST['remark'] : '';
        $supp_name1 = $_POST['supp_name1'] ? $_POST['supp_name1'] : '';
        $supp_ttl1 = $_POST['supp_ttl1'] ? $_POST['supp_ttl1'] : '';
        $supp_dob1 = $_POST['supp_dob1'] ? $_POST['supp_dob1'] : '';
        $hub_supp1 = $_POST['hub_supp1'] ? $_POST['hub_supp1'] : '';
        $supp_mmn1 = $_POST['supp_mmn1'] ? $_POST['supp_mmn1'] : '';



        $supp_name2 = $_POST['supp_name2'] ? $_POST['supp_name2'] : '';
        $supp_ttl2 = $_POST['supp_ttl2'] ? $_POST['supp_ttl2'] : '';
        $supp_dob2 = $_POST['supp_dob2'] ? $_POST['supp_dob2'] : '';
        $hub_supp2 = $_POST['hub_supp2'] ? $_POST['hub_supp2'] : '';
        $supp_mmn2 = $_POST['supp_mmn2'] ? $_POST['supp_mmn2'] : '';

        $supp_name3 = $_POST['supp_name3'] ? $_POST['supp_name3'] : '';
        $supp_ttl3 = $_POST['supp_ttl3'] ? $_POST['supp_ttl3'] : '';
        $supp_dob3 = $_POST['supp_dob3'] ? $_POST['supp_dob3'] : '';
        $hub_supp3 = $_POST['hub_supp3'] ? $_POST['hub_supp3'] : '';
        $supp_mmn3 = $_POST['supp_mmn3'] ? $_POST['supp_mmn3'] : '';

        $notes_1 = $_POST['notes_1'] ? $_POST['notes_1'] : '';
        $notes_2 = $_POST['notes_2'] ? $_POST['notes_2'] : '';
        $notes_3 = $_POST['notes_3'] ? $_POST['notes_3'] : '';


        $kode_pos = $_POST['kode_pos'] ? $_POST['kode_pos'] : '';
        $catatan_1 = $_POST['catatan_1'] ? $_POST['catatan_1'] : '';
        $catatan_2 = $_POST['catatan_2'] ? $_POST['catatan_2'] : '';

        //$parent_id = $_POST['parent_id'] ? $_POST['parent_id'] : '';

        //reset status
        $where = array(
            "id_prospect" => $id_prospect,
            "id_user" => $this->id_user,
        );



        //var_dump($where);
        $data_toupdate = array(
            "is_active" => "0"
        );
        $this->db->update("tb_prospect_pickup", $data_toupdate, $where);

        //add new record






        $data = array(
            "id_prospect" => $id_prospect,
            "id_user" => $this->id_user,
            "remark" => $remark,
            "supp_name1" => $supp_name1,
            "supp_ttl1" => $supp_ttl1,
            "supp_dob1" => $supp_dob1,
            "hub_supp1" => $hub_supp1,
            "supp_mmn1" => $supp_mmn1,

            "supp_name2" => $supp_name2,
            "supp_ttl2" => $supp_ttl2,
            "supp_dob2" => $supp_dob2,
            "hub_supp2" => $hub_supp2,
            "supp_mmn2" => $supp_mmn2,

            "supp_name3" => $supp_name3,
            "supp_ttl3" => $supp_ttl3,
            "supp_dob3" => $supp_dob3,
            "hub_supp3" => $hub_supp3,
            "supp_mmn3" => $supp_mmn3,

            "notes_1" => $notes_1,
            "notes_2" => $notes_2,
            "notes_3" => $notes_3,

            "kode_pos" => $kode_pos,
            "catatan_1" => $catatan_1,
            "catatan_2" => $catatan_2,

            "created" => date("Y-m-d H:i:s"),
            "is_active" => 1
        );
        $this->db->insert("tb_prospect_pickup", $data);

        //update status
        $where = array(
            "id_prospect" => $id_prospect
        );
        $data_toupdate = array(
            "id_app_status" => "4"
        );
        $this->db->update("tb_prospect", $data_toupdate, $where);


        //var_dump($data_toupdate);
        //die("ddd");

        //calltrack
        $remark = "";
        //$id_callcode = $parent_id;
        //$notelp = $no_contacted;
        $begin_call_time = "";
        $idle_time = "";
        $end_call_time = "";
        $total_call_time = "";
        $date = date("Y-m-d H:i:s");

        /*
      $this->prospect_model->add_calltrack($id_prospect, $notelp="", $this->id_user,
      	$id_callcode, '', date("Y-m-d"), date("H:i:s"), '', $remark, '', '', $begin_call_time,
        $end_call_time, $idle_time, $total_call_time);
        */

        $data['txt_title'] = "<h4>Call Activity has been saved.</h4>";


        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/main_3', $data);
        $this->load->view('tsr/footer', $data);
    }


    public function agree_referral_mgm($id_prospect, $id_product = '')
    {
        $data["txt_title"] = "Agree " . $id_prospect;
        $post = @$_POST['post'];

        //var_dump($_POST);
        //die('');

        // save detail product
        if ($post) {

            $post = array();


            foreach ($_POST as $key => $value)
                $post[$key] = $this->input->xss_clean($value);

            //var_dump($_POST);

            $arrdata = $post;
            $arrdata['id_user'] = $this->id_user;
            $arrdata['id_prospect'] = $id_prospect;
            $arrdata['created_date'] = date("Y-m-d") . ' ' . date("H:i:s");
            //$pengiriman = $arrdata['pengiriman'];
            //$nama = $arrdata['nama'];

            //echo $pengiriman;

            //var_dump($arrdata);

            $id_user = $arrdata['id_user'];
            //die();





            //$faxno = '9'.$arrdata['fax_number'].'#456798';						
            //$faxno = $arrdata['fax_number'];
            //$mailto = $arrdata['mailto'];
            $jenis_aplikasi = $arrdata['is_fax'];

            // get fullname
            $sql3 = "select * from tb_prospect where id_prospect = '$id_prospect'";
            //echo $sql3;
            $q3 = $this->db->query($sql3);
            $data1 = $q3->row_array();

            $fullname = $data1['fullname'];
            $id_spv = $data1['id_spv'];


            //$this->prospect_model->get_fullname($id_prospect);



            //prospect print


            //echo 'fax_numberrrrrrr'$faxno;



            //

            $this->prospect_model->add_prospect_print($arrdata);
            $this->prospect_model->add_calltrack($id_prospect, '', $this->id_user, 44, '', date("Y-m-d"), date("H:i:s"), '', '', '', '', '', '', '', '');
            $this->prospect_model->set_is_agree($id_prospect, $id_product);
            //insert winfax



            $fax_number = $arrdata['fax_number'];
            $mailto = $arrdata['mailto'];

            if (TRIM($fax_number) != '') {
                $this->prospect_model->tb_winfax($id_prospect, $jenis_aplikasi, $fullname, $fax_number, $id_user, $id_spv);
            }

            if (TRIM($mailto) != '') {
                //insert email            
                $this->prospect_model->tb_email($id_prospect, $jenis_aplikasi, $fullname, $mailto, $id_user, $id_spv);
            }

            /*
            if($pengiriman==1){
							//echo "masuk pengiriman 1";
							//echo $nama;
							$this->prospect_model->tb_winfax($id_prospect,$jenis_aplikasi,$fullname,$nama,$id_user);
						}
						
						else{
							//echo "masuk pengiriman 2";
							$this->prospect_model->tb_email($id_prospect,$jenis_aplikasi,$fullname,$nama,$id_user);
						}
            */





            //die("dorr");

            //redirect('tsr/main');
            // yg dipake di agree referral redirect("tsr/main_print/$id_prospect");
            /*
           //cek cttype kalo 3 berarti incoming
           if()
           {
           }
 */

            //$sql=$this->db->query("select *, a.id_prospect, a.fullname from tb_campaign as b inner join tb_prospect as a on a.id_campaign = b.id_campaign");
            $sql = $this->db->query("select a.id_prospect, b.campaign_type, a.fullname from tb_campaign as b
            											inner join tb_prospect as a on a.id_campaign = b.id_campaign 
            											where a.id_prospect=$id_prospect");
            $row = $sql->row_array();
            $data = $row["campaign_type"];
            //var_dump($data);
            //die();
            if ($data == 3) {
                $record_close->id_app_status = 4;
                $this->db->update($this->tb_prospect, $record_close, array('id_prospect' => $id_prospect));
                //var_dump($this->db->last_query());
                //die();
                // echo("dididi");
                redirect("tsr/main_print/$id_prospect");
            } else {
                //die("dododo");
                redirect("tsr/main_print/$id_prospect");
            }

            //var_dump($this->db->last_query());
            //die();
        } else {
            $data["id_product"] = $id_product;

            //get prospect
            $q = $this->db->query("select * from tb_prospect where id_prospect=$id_prospect");
            $row_prospect = $q->row_array();
            $data["row_prospect"] = $row_prospect;
        }

        //script tele
        $sql = "select * from tb_product where published=1";
        $q = $this->db->query($sql);

        $row_script = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row_script[] = $row;
            }
        }

        $data['scripts'] = $row_script;

        //script tele 2	

        $sql1 = "select * from tb_script group by jenis_dokumen";

        $q1 = $this->db->query($sql1);



        $row_script1 = array();
        if ($q1->num_rows() > 0) {
            foreach ($q1->result_array() as $row1) {
                $row_script1[] = $row1;
            }
        }

        $data['scripts1'] = $row_script1;

        //script tele 2	


        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/agree_referral', $data);
        $this->load->view('tsr/footer', $data);
    }



    public function input_referensi($id_prospect)
    {

        //echo $id_prospect.'inpt referensi';

        $data["id_prospect"] = $id_prospect;

        $sql = "select * from tb_prospect where id_prospect = '$id_prospect'";
        $query = $this->db->query($sql);
        $row = $query->row_array();
        //var_dump($row);
        //die ('');


        $fullname_referensi = $row['fullname'];
        $cif_no = $row['cif_no'];
        //echo $fullname ;  	  	
        //$post = @$_POST['post'];

        $data["txt_title"] = "Input Referensi";
        $post = $this->input->post('post');

        if ($post) {

            $nama = $this->input->post('nama') ? $this->input->post('nama') : '';
            $hp1 = $this->input->post('hp1') ? $this->input->post('hp1') : '';
            $telp2 = $this->input->post('telp2') ? $this->input->post('telp2') : '';
            $telp3 = $this->input->post('telp3') ? $this->input->post('telp3') : '';
            $ya = $this->input->post('ya') ? $this->input->post('ya') : '';
            $tidak = $this->input->post('tidak') ? $this->input->post('tidak') : '';

            if ($nama != '') {
                //$this->prospect_model->input_referensi($id_prospect, $nama, $hp1, $telp2, $telp3, $ya, $tidak);
                //$this->prospect_model->upload_prospect();
                //$this->prospect_model->upload_prospect_done();   		


                $sql = "select * from tb_prospect where hp1 = '$hp1' 
    						and DATE(createdate) BETWEEN DATE_ADD(CURDATE(), INTERVAL-6 MONTH) AND CURDATE()";


                //echo $sql;

                $q = $this->db->query($sql);

                if ($q->num_rows > 0) {

                    $data['txt_error'] = 'Data sudah ada, input data lain';
                } else {

                    $this->prospect_model->upload_prospect_1($id_prospect, $nama, $hp1, $telp2, $telp3, $ya, $tidak, $fullname_referensi, $cif_no);
                    //$this->prospect_model->mgm_tbo($id_prospect);

                }
            }
        }






        $this->load->view('tsr/header', $data);
        $this->load->view('tsr/input_referensi', $data);
        $this->load->view('tsr/footer', $data);
    }


    public function tbo($id_prospect)
    {

        $data["id_prospect"] = $id_prospect;
        $data["txt_title"] = "Input Referensi";

        $id_prospect = $this->input->post("id_prospect");

        //echo 'id_tbo ='.$id_prospect;

        $sql = "SELECT * FROM tb_prospect
    					WHERE id_prospect = '$id_prospect'     		
    					AND is_agree = '1'
    					AND last_id_callcode = '44'    								
    	";

        $q = $this->db->query($sql);

        if ($q->num_rows() > 0) {

            $createdate = date("Y-m-d H:i:s");
            $last_calltime = date("Y-m-d H:i:s");

            $sql = "update tb_prospect
       			 set id_app_status=3, 
        		 last_id_callcode = 35,
        		 createdate = '$createdate', last_calltime = '$last_calltime'
       			 where id_prospect=$id_prospect
       			 ";

            $q = $this->db->query($sql);
        }

        redirect('tsr/input_referensi/' . $id_prospect . '');
    }

    function last_callhistory($id_prospect = 0, $limit = 10)
    {
        $qryAgent = $this->load->database('agent', true);
        $curdate = DATE('Y-m-d');
        if ($id_prospect != 0) {
            $qryAgent->where('tb_calltrack.id_prospect', $id_prospect);
        } else {
            $qryAgent->where('id_user', $this->id_user);
        }
        $qryAgent->select('tb_calltrack.remark AS call_notes, tb_calltrack.*, tb_prospect.*, tb_callcode.*', FALSE);
        $qryAgent->where('tb_calltrack.call_date', $curdate);
        $qryAgent->where('tb_calltrack.id_callcode <>', 0);
        $qryAgent->where_not_in('tb_calltrack.id_callcode', array('1', '2'));
        $qryAgent->join('tb_prospect', 'tb_prospect.id_prospect = tb_calltrack.id_prospect', 'LEFT');
        $qryAgent->join('tb_callcode', 'tb_callcode.id_callcode = tb_calltrack.id_callcode', 'LEFT');
        $qryAgent->limit($limit);
        $qryAgent->order_by('id_calltrack', 'DESC');

        $qObj = $qryAgent->get('tb_calltrack');

        $this->load->model('tsr/callcode_model', 'callcode_model');
        $callcodeModel = $this->callcode_model;
        $data['callcodeModel'] = $callcodeModel;

        if ($qObj->num_rows() > 0) {
            $data['list'] = $qObj->result_array();
        } else {
            $data['list']  = array();
        }

        return $this->load->view('tsr/last_calltrack', $data, TRUE);
    }

    function get_listprogram()
    {
        $this->db->where('is_active', 1);
        $qObj = $this->db->get('tb_program');

        if ($qObj->num_rows() > 0) {
            return $qObj->result_array();
        } else {
            return array();
        }
    }

    function get_listtravelagent()
    {
        $this->db->where('is_active', 1);
        $qObj = $this->db->get('tb_travelagent');

        if ($qObj->num_rows() > 0) {
            return $qObj->result_array();
        } else {
            return array();
        }
    }

    function get_camptype($id_campaign)
    {

        $this->db->select('campaign_type');
        $this->db->where('id_campaign', $id_campaign);
        $qObj = $this->db->get('tb_campaign');

        if ($qObj->num_rows() > 0) {
            $qArr = $qObj->row_array();
            $campType = $qArr['campaign_type'];
        } else {
            $campType = "0";
        }

        return $campType;
    }

    function update_newpriority($id_prospect)
    {

        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);

        $qObj = $this->db->get('tb_prospect');
        $prospectData = $qObj->row_array();
        if ($prospectData['is_newpriority'] == '1') {
            $toUpdate = array(
                'is_newpriority' => 0,
                'date_priorityresponse' => DATE('Y-m-d H:i:s')
            );
            $this->db->where('id_prospect', $id_prospect);
            $this->db->update('tb_prospect', $toUpdate);
        }
    }

    function update_recycle($id_prospect)
    {

        $this->db->where('id_prospect', $id_prospect);
        $this->db->limit(1);

        $qObj = $this->db->get('tb_prospect');
        $prospectData = $qObj->row_array();
        if ($prospectData['is_recycle'] == '1') {
            $toUpdate = array(
                'is_recycle' => 0
            );
            $this->db->where('id_prospect', $id_prospect);
            $this->db->update('tb_prospect', $toUpdate);
        }
    }

    function assign_courier($pku_zone = '', $pku_date = '')
    {
        $return = 0;
        if ($pku_zone == '' or $pku_date == '') {
            return $return;
        }
        $this->load->model('kurir/assignment_model', 'assign');

        ## Find courier list by Zone
        $courierList = $this->assign->find_activeCourier($pku_zone);
        if (COUNT($courierList) > 0) {
            $handleInfo = $this->assign->find_courierHandleInfo($pku_date, $courierList);
            if (COUNT($handleInfo) > 0) {
                foreach ($handleInfo as $info) {
                    $is_handleable = $this->assign->is_handleable($info);
                    if ($is_handleable == 1) {
                        $return = $info['pku_courier'];
                        break;
                    }
                }
            } else {
                $return = $courierList[0];
            } //all courier not get task yet and use random 1 courier
        }
        return $return;
    }

    function generate_randomcheck($id_user = 0, $id_prospect = 0)
    {
        $this->load->model('misc/misc_model');
        $this->load->model('randomhearing_model');

        if ($id_user != 0) {
            ## GET Leader 
            $id_leader = $this->misc_model->get_tableDataById('tb_users', $id_user, 'id_user', 'id_leader');

            $current_bucket = $this->randomhearing_model->get_curbucket($id_user, $id_leader); // Jumlah data Agree Sekarang, dan berapa yang sudah disetting harus dilisten di bucket TSR ini. 

            $xlistenBelowObj = $this->randomhearing_model->get_xlistenbelow($id_user, $id_leader, $current_bucket['xlisten']);
            $is_rows = $xlistenBelowObj->num_rows();

            $cur_progressObj = $this->randomhearing_model->get_spvbucketprogress($id_leader);
            $cur_progress = $cur_progressObj->row_array();
            $xlisten_count = intval($cur_progress['xlisten']);

            if ($is_rows == 0) {

                if ($xlisten_count < 10) {
                    ## Update Must Listen
                    $update = array('is_randomchecked' => 1); // set must listen
                    $this->randomhearing_model->upd_randomlistenstatus($id_prospect, $update);
                } else {
                    ## Set to Hold
                    $update = array('is_randomchecked' => 0); // set must listen ( holded )
                    $this->randomhearing_model->upd_randomlistenstatus($id_prospect, $update);
                }
            } else {
                ## Set to Hold
                $update = array('is_randomchecked' => 0); // set must listen ( holded )
                $this->randomhearing_model->upd_randomlistenstatus($id_prospect, $update);
            }
        }
    }

    function check_holdedrandom($id_user = 0, $id_prospect = 0)
    {
        $this->load->model('misc/misc_model');
        $this->load->model('randomhearing_model');
        //var_dump($id_user);
        $id_leader = $this->misc_model->get_tableDataById('tb_users', $id_user, 'id_user', 'id_leader');

        if ($id_user != 0) {
            $current_bucket = $this->randomhearing_model->get_curbucket($id_user, $id_leader);

            $xlistenBelowObj = $this->randomhearing_model->get_xlistenbelow($id_user, $id_leader, $current_bucket['xlisten']);
            $is_rows = $xlistenBelowObj->num_rows();
            //echo $this->db->last_query(); var_dump($is_rows); 

            $cur_progressObj = $this->randomhearing_model->get_spvbucketprogress($id_leader);
            $cur_progress = $cur_progressObj->row_array();
            //var_dump($cur_progress);

            $xlisten_count = intval($cur_progress['xlisten']); // current listen count
            $holdedrandomObj = $this->randomhearing_model->get_holdedrandom($id_leader);
            $holdedrandomArr = $holdedrandomObj->result_array();
            //var_dump($holdedrandomArr); die(); 

            if ($is_rows == 0) {
                foreach ($holdedrandomArr as $holded) {
                    if ($xlisten_count < 10) {
                        $update = array('is_randomchecked' => 1);  //set must listen ( holded )
                        $this->randomhearing_model->upd_unholdrandom($holded['id_prospect'], $update);
                        $xlisten_count++;
                    } else {
                        if (($xlisten_count / intval($cur_progress['xagree']) * 100) < 10) {
                            $update = array('is_randomchecked' => 1);  //set must listen ( holded )
                            $this->randomhearing_model->upd_unholdrandom($holded['id_prospect'], $update);
                            $xlisten_count++;
                        }
                    }
                }
            }
        } // invalid user id
    }

    function incall_proxy($id_prospect)
    {

        $qryAgent = $this->load->database('agent', true);

        ## Get User Data
        $qryAgent->where('id_user', $_SESSION['id_user']);
        $qObj = $qryAgent->get('tb_users');
        $userData = $qObj->row_array();

        ## Get Leader Data
        $qryAgent->where('id_user', $userData['id_leader']);
        $qObj = $qryAgent->get('tb_users');
        $leaderData = $qObj->row_array();

        ## Get Manager Data
        $qryAgent->where('id_user', $leaderData['id_leader']);
        $qObj = $qryAgent->get('tb_users');
        $managerData = $qObj->row_array();

        $qryAgent->where('id_prospect', $id_prospect);
        $qObj = $qryAgent->get('tb_prospect');
        $prospectArr = $qObj->row_array();


        $this->load->database('default');

        ## Update Data Position
        $update = array(
            'id_tsm' => $managerData['id_user'],
            'id_spv' => $leaderData['id_user'],
            'id_tsr' => $userData['id_user']
        );
        if ($prospectArr['date_tsr'] == '0000-00-00') {
            $update['date_tsm'] = DATE('Y-m-d');
            $update['date_spv'] = DATE('Y-m-d');
            $update['date_tsr'] = DATE('Y-m-d');
        }
        $this->db->where('id_prospect', $id_prospect);
        $this->db->update('tb_prospect', $update);

        ## Redirect to Main Screen
        redirect('tsr_v2/main/' . $prospectArr['id_campaign'] . '/' . $id_prospect);
    }

    function updateredialcalltrack()
    {
        
        ## Update Data calltrack
        $calltrackData = array(
            'id_callcode' => 187, //187 == redial
            'remark' => 'tso melakukan redial',
            'id_product' => $this->input->post('id_product'),
            'outcall_id' => $this->input->post('outcall_id_submit'),
            'caller_id' => $this->input->post('caller_id_submit'),
            'outcall_start' => $this->input->post('outcall_start_submit'),
            'outcall_duration' => $this->input->post('outcall_sec_realtime_submit'),
            'rec_id' => $this->input->post('rec_id_submit'),
            'rec_filename' => $this->input->post('rec_filename_submit'),
            'end_call_time' => DATE('Y-m-d H:i:s')
        );
        $this->db->where('id_calltrack', $this->input->post('id_calltrack'));
        
        $update_status = $this->db->update('tb_calltrack', $calltrackData);
        if ($update_status) {
        // if (true) {
            echo json_encode(array('status' => true, 'message' => 'Data update successfully'));
        } else {
            echo json_encode(array('status' => false, 'message' => 'Failed to upudate data'));
        }
    }
}
