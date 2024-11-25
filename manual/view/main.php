<meta http-equiv="Cache-Control" content="must-revalidate" />

<style>
  fieldset {
    padding-left: 15px;
  }

  legend {
    font-weight: bold;
    color: black;
    font-size: 12px;
    margin: 5px;
    padding: 5px; 
  }

  .martin {
    display: none;
    border: 1px solid silver;
    border-radius: 8px;
    box-shadow: 2px 2px 3px gray;
    position: fixed;
    width: 300px;
    height: 70px;
    bottom: 5px;
    right: 0px;
    background-color: RGBA(240, 220, 240, 0.5);
    color: #000;
    z-index: 9;
  }

  .martin2nd {
    display: none;
    border: 1px solid silver;
    border-radius: 8px;
    box-shadow: 2px 2px 3px gray;
    position: fixed;
    width: 300px;
    height: 70px;
    bottom: 85px;
    right: 0px;
    background-color: RGBA(240, 220, 240, 0.5);
    color: #000;
    z-index: 9;
  }

  .martin:hover,
  .martin2nd:hover {
    background-color: RGBA(144, 157, 220, 0.5);
  }

  .rmartin,
  .rmartin2nd {
    width: auto;
    float: right;
    font-size: 13px;
    padding: 2px 5px 0px 15px;
    color: blue;
  }

  .addinfo {
    word-break: break-all;
    word-wrap: break-word;
    overflow: scroll;
  }

  .hide {
    display: none;
  }

  #zipcode_finder,
  #editzone_container {
    width: 50%;
    height: 90%;
    position: fixed;
    right: -2000px;
    top: 0px;
    border: 1px solid Gray;
    border-radius: 8px;
    background: #F5F5F5;
    border: 0px;
    box-shadow: 2px 2px 5px 2px LightGray;
    padding: 10px;
    border-radius: 8px;
    padding: 5px;
  }

  #zipcode_finder p {
    text-align: Right;
    margin-right: 10px;
  }

  #zipcode_finder fieldset {
    border-radius: 8px;
    background: #DDDDDD;
    border: 1px solid #DDDDDD;
  }

  #installment_simulator,
  #editzone_container {
    width: 45%;
    height: 60%;
    position: fixed;
    right: -2000px;
    top: 0px;
    border: 1px solid Gray;
    border-radius: 8px;
    background: #F5F5F5;
    border: 0px;
    box-shadow: 2px 2px 5px 2px LightGray;
    padding: 10px;
    border-radius: 8px;
    padding: 5px;
  }

  #installment_simulator p {
    text-align: Right;
    margin-right: 10px;
  }

  #installment_simulator fieldset {
    border-radius: 8px;
    background: #DDDDDD;
    border: 1px solid #DDDDDD;
  }


  #installment_simulator_FP,
  #editzone_container {
    width: 45%;
    height: 60%;
    position: fixed;
    right: -2000px;
    top: 0px;
    border: 1px solid Gray;
    border-radius: 8px;
    background: #F5F5F5;
    border: 0px;
    box-shadow: 2px 2px 5px 2px LightGray;
    padding: 10px;
    border-radius: 8px;
    padding: 5px;
  }

  #installment_simulator_FP p {
    text-align: Right;
    margin-right: 10px;
  }

  #installment_simulator_FP fieldset {
    border-radius: 8px;
    background: #DDDDDD;
    border: 1px solid #DDDDDD;
  }

  #installment_simulator_COP,
  #editzone_container {
    width: 45%;
    height: 60%;
    position: fixed;
    right: -2000px;
    top: 0px;
    border: 1px solid Gray;
    border-radius: 8px;
    background: #F5F5F5;
    border: 0px;
    box-shadow: 2px 2px 5px 2px LightGray;
    padding: 10px;
    border-radius: 8px;
    padding: 5px;
  }

  #installment_simulator_COP p {
    text-align: Right;
    margin-right: 10px;
  }

  #installment_simulator_COP fieldset {
    border-radius: 8px;
    background: #DDDDDD;
    border: 1px solid #DDDDDD;
  }


  #installment_simulator_PL,
  #editzone_container {
    width: 45%;
    height: 60%;
    position: fixed;
    right: -2000px;
    top: 0px;
    border: 1px solid Gray;
    border-radius: 8px;
    background: #F5F5F5;
    border: 0px;
    box-shadow: 2px 2px 5px 2px LightGray;
    padding: 10px;
    border-radius: 8px;
    padding: 5px;
  }

  #installment_simulator_PL p {
    text-align: Right;
    margin-right: 10px;
  }

  #installment_simulator_PL fieldset {
    border-radius: 8px;
    background: #DDDDDD;
    border: 1px solid #DDDDDD;
  }

  #areaTable tr {
    overflow: visible;
    border: 1px solid LightGray;
  }

  .td1 {
    width: 10%;
  }

  .td2 {
    width: 20%;
  }

  .td3 {
    width: 20%;
  }

  .td4 {
    width: 15%;
  }

  .td5 {
    width: 15%;
  }

  .td6 {
    width: 10%;
  }

  .refresh {
    color: DarkBlue;
    font-weight: bold;
  }
</style>

<script type="text/javascript">
  $(document).ready(function() {

    setTimeout(function() {
      checkSendback();
    }, 5000);
    setTimeout(function() {
      checkReminder();
      //checkPrioritize();
    }, 3000);
    setTimeout(function() {
      checkMessage();
    }, 2000);

    var id_product = $('#id_product').val();
    var is_multiproduct = <?= @$prospect['multiproduct'] == 1 ? 1 : 0; ?>;
    //alert();      
    //checkTimezone();
    checkCallsession();
    checkCallWeight();
    agreebtn_controller();
    readconfig();
    //CheckVerify();
    autobrowse();
    autofillSimulator();
    $('#multi_subremis').show('fast');
    //$('#multi_offer').show('fast');
    if (id_product != '40' && id_product != '59' & id_product != '39') {
      $('[id="btn-agree"]').removeClass('hide');
    }
    if (is_multiproduct == 1) {
      $('[id="btn-agree"]').removeClass('hide');
    }



  });

  <?php if ($prospect['is_priority'] == 2) { ?>

    function doSipCallfirst2(url, value, value2, inbound_uniqueid) {
      console.log(url);
      if (value != "") {
        var no_contacted = value;
        var val = [];
        $(':checkbox:checked').each(function(i) {
          val[i] = $(this).val();
        });

        var st_id_product = val.join("|").toString();
        jQuery.post(url, {
            no_contacted: value,
            id_notelp: value2,
            inbound_uniqueid: inbound_uniqueid,
            username: jQuery('#username').val(),
            id_user: jQuery('#id_user').val(),
            id_spv: jQuery('#id_spv').val(),
            id_tsm: jQuery('#id_tsm').val(),
            id_prospect: jQuery('#id_prospect').val(),
            // id_product : st_id_product,
            id_product: jQuery('#id_product').val(),
            id_campaign: jQuery('#id_campaign_hid').val(),
            id_calltrack: jQuery('#id_calltrack').val(),
            id_callcode: jQuery('#id_callcode').val(),
            call_attempt: jQuery('#call_attempt').val(),
            remark: jQuery('#remark').val(),
            //id_product :  jQuery('input:radio[name=id_product]:checked').val(),
            post: true
          },
          function(html) {
            jQuery.unblockUI();
            telli(html);
            $('.copHide').show();
          });
      }
    }
  <?php } ?>

  function autobrowse() {
    var camp = "<?= $this->uri->segment(3); ?>";
    var prospect = "<?= $this->uri->segment(4); ?>";
    if (camp != "" && prospect == "") {
      $(".btn-browse").click();
    }
  }

  function autofillSimulator() {
    <?php if (!empty(@$refreshbagcop)) : ?>
      var nominal = "<?= @$refreshbagcop['max_loan']; ?>";
    <?php elseif (!empty($refreshbag) && !in_array("CPIL", json_decode($offers[0]['xsell_cardxsell']))) : ?>
      var nominal = "<?= @$refreshbag['max_loan']; ?>";
    <?php else : ?>
        var nominal = "<?= @$prospect['max_loan']; ?>";
    <?php endif; ?>
    var tenor = 6;
    var bunga = 0.89;

    $('#simulator_sum').val(nominal);
    $('#simulator_sum1').val(nominal);
    $('#simulator_tenure').val(tenor);
    $('#simulator_interest_monthly').val(bunga);

    simulateInstallment();
  }

  function readconfig() {
    var pre_validation = '<?= $pre_validation[0]['value']; ?>';

    var pre_validation = '1';
    if (pre_validation == '0') {
      $('#btn-additional').removeClass('hide');
      $('#btn-agree').removeClass('hide');
      $('#btn-verf').addClass('hide');
      //$('#multi_offer').removeClass('hide');
      $('[id^="btn-agree"]').removeClass('hide');
    }
  }

  function CheckVerify() {
    var idprospect = "<?= $this->uri->segment(4); ?>";
    var agentname = '<?= $_SESSION["id_user"] ?>';
    var ajaxUrl = "<?= site_url(); ?>ajax/tsr/check_verifysession/" + idprospect + '/' + agentname;
    //alert(ajaxUrl);
    $.ajax({
      url: ajaxUrl,
      type: "POST",
      data: {
        idprospect: idprospect,
        outcall_id: outcall_id
      },
      success: function(resp) {
        $('#failcount').val(resp);
        alert(resp);
        /*if((resp*1) > 1){
            $('#cur_try_inp_hp1').html(2);
            $('#btn-verf').addClass('hide');
            //$('#verif').show();
            $('#remis').remove();
        } else if((resp*1) < 0){
            //$('#cur_try_inp_hp1').html(Math.round(resp));
            //$('#btn-verf').addClass('hide');
             $('#btn-verf').removeClass('hide');
        }*/
      }
    });
  }

  function agreebtn_controller() {
    agreebtn_validator('<?= site_url(); ?>ajax/tsr/agreebtn_validator/<?= @$prospect['id_prospect'] ?>');

  }

  function agreebtn_controller_v2() {
    $('#' + $('#xsell').val()).hide();
  }

  function checkMessage() {
    var flag = $('#message_popup').val();
    if (flag == '0') {
      do_checkMessage();
    } else {
      setTimeout(function() {
        checkMessage();
      }, 10000);
    }

    function do_checkMessage() {
      var xurl = "<?= site_url(); ?>ajax/message/get_newmessage";
      $.ajax({
        url: xurl,
        type: "POST",
        success: function(resp) {
          if (resp != '-') {
            $('#message_popup').val(1);
            var myboxywin = new Boxy(resp, {
              modal: true,
              unloadOnHide: true,
              title: "New Message",
              closeable: false
            });
          }
          setTimeout(function() {
            checkMessage();
          }, 15000);
        }
      });
    }
  }

  function checkCallWeight() {
    var id_prospect = $('#id_prospect').val();
    if (!id_prospect) {
      return;
    }
    var cur_weight = $('#last_weight').val() * 1;
    var lastagree = $('#last_agree').val() * 1;
    var xobj = $("input[id^='btncall-parent-']");
    $.each(xobj, function(x, y) {
      var weight = $(y).attr('data-weight') * 1;
      if (weight < cur_weight) {
        $(y).hide();
      } else if (lastagree == 1) {
        $(y).hide();
      }
    });
  }

  function checkCallsession() {
    //alert( $('#id_prospect').val() );
    var id_prospect = $('#id_prospect').val();
    if (!id_prospect) {
      return;
    }
    do_checkCallsession();

    function do_checkCallsession() {
      var xurl = "<?= site_url(); ?>ajax/tsr/checkCallSession";
      $.ajax({
        url: xurl,
        type: "POST",
        data: {
          id_prospect: id_prospect
        },
        success: function(resp) {
          var json = $.parseJSON(resp);
          if (json.is_valid == '0') {
            $('.dial-home, .dial-office, .dial-mobile').hide();
            Boxy.alert('Kamu tidak bisa membuka data ini sebelum melakukan submit hasil call sebelumnya, layar anda akan dialihkan ke nasabah sebelumnya');
            setTimeout(function() {
              location.href = json.ss_redirect;
            }, 3000);
          } else if (json.is_valid == '1') {
            var id_calltrack = $('#id_calltrack').val();
            if (id_calltrack != json.ss_id_calltrack) {
              //restore call session
              var myBoxy = new Boxy('<p>System restoring your call session</p>', {
                modal: true
              });
              $('.dial-home, .dial-office, .dial-mobile').hide();
              $('#no_contacted').val(json.ss_no_contacted);
              $('#id_calltrack').val(json.ss_id_calltrack);
              setTimeout(function() {
                myBoxy.hideAndUnload();
              }, 1000);
            }
          } else {
            // do Nothing system not found any locked session
          }

          setTimeout(function() {
            checkCallsession();
          }, 5000);
        }
      });

    }
  }

  function checkReminder() {
    var xurl = "<?php echo site_url(); ?>ajax/tsr/checkReminder/";
    $.ajax({
      url: xurl,
      type: "POST",
      data: {
        id_user: "<?php echo $_SESSION['id_user']; ?>"
      },
      success: function(resp) {
        if (resp != '-') {
          var json = $.parseJSON(resp);
          var callnow = "<?php echo site_url(); ?>ajax/tsr/showReminder/" + json.id_reminder + "/";
          new Boxy('<table style="width:600px"><tr> <th colspan="2">Callback Reminder</th></tr><tr><td><img src="<?php echo base_url() . "component/images/calling.gif" ?>"/></td><td><table style="width:600px"><tr class="a"><td>Nama</td><td>' + json.fullname + '</td></tr><tr class="b"><td>Tanggal</td><td>' + json.re_date + '</td></tr><tr class="a"><td>Jam</td><td>' + json.re_time + '</td></tr><tr class="b"><td>Remark</td><td>' + json.remark + '</td></tr><tr class="a"><td>Action</td><td><a href="' + callnow + '">Call Now</a> </td></tr></table></td></tr></table>', {
            title: "Callback Reminder",
            closetext: "[close]"
          });
        }
      }
    });
  }

  function checkSendback() {
    //alert('a');
    var xurl = "<?php echo site_url(); ?>ajax/tsr/checkSendback/";

    $.ajax({
      url: xurl,
      type: "POST",
      data: {
        id_user: "<?php echo $_SESSION['id_user']; ?>"
      },
      timeout: 30000,
      success: function(resp) {
        resp = resp * 1;
        if (resp > 0) {
          $('#sendback_count').html(resp);
          $('.martin2nd').slideDown('slow');
        }
      }
    });
  }

  function checkTimezone() {
    var timezone = "<?php echo @$prospect['timezone']; ?>";
    var id_prospect = "<?= @$prospect['id_prospect']; ?>";
    if (id_prospect == '') {
      return;
    }
    timezone = timezone.toUpperCase();
    cur_hour = parseInt("<?php echo DATE('H') ?>");
    if (timezone == 'WIB') {
      if (cur_hour <= 7 || cur_hour >= 18) {
        showAlert('WIB');
      }
    } else if (timezone == 'WITA') {
      if (cur_hour <= 6 || cur_hour >= 17) {
        showAlert('WITA');
      }
    } else if (timezone == 'WIT') {
      if (cur_hour <= 5 || cur_hour >= 16) {
        showAlert('WIT');
      }
    } else {
      if (cur_hour <= 5 || cur_hour >= 16) {
        showAlert('NOZONE');
      }
    }

    function showAlert(zone) {
      var xurl = "<?= site_url() . 'ajax/timezone/show_timezonewarning/'; ?>" + zone;
      $.ajax({
        url: xurl,
        type: "POST",
        success: function(html) {
          new Boxy(html, {
            modal: true,
            closeable: true,
            title: "Timezone Warning"
          });
        }
      });
    }
  }

  function checkPrioritize() {

    var id_campaign = "<?= $this->uri->segment(3); ?>";
    var id_prospect = "<?= $this->uri->segment(4); ?>";

    if (id_campaign != '' || id_prospect != '') {
      return;
    }

    var xurl = "<?php echo site_url(); ?>ajax/tsr/checkPrioritize/";
    $.ajax({
      url: xurl,
      type: "GET",
      success: function(html) {
        $('#priority_count').html(html);
        html = html * 1;
        if (html > 0) {
          $('.martin').slideDown('slow');
        }
      }
    });
  }

  function hidepinfo() {
    $('.martin').slideUp('fast');
  }

  function hidepinfo2nd() {
    $('.martin2nd').slideUp('fast');
  }

  function go_contacted(call_code) {
    var id_prospect = jQuery('#id_prospect').val();
    var id_product = $('#id_product').val();
    var id_calltrack = jQuery('#id_calltrack').val();
    var no_contacted = jQuery('#no_contacted').val();

    if (no_contacted == '') {
      telli('Please Dial Before Submiting Calltrack !');
      return;
    }

    var st_url = '<?= site_url() ?>/tsr/get_option_callcode/' + call_code + '/' + id_prospect + '/' + id_product + '/' + id_calltrack + '/' + no_contacted;
    //alert(st_url);
    $('.det-right').fadeIn('slow');
    optionCodeCallTrack(st_url);
    $('#subremis').slideUp('slow');
    $('#multi_offer').slideUp('fast');
    $('#connect').fadeIn();
  }

  function show_contacted() {
    var no_contacted = jQuery('#no_contacted').val();

    var lastagree = $('#last_agree').val();

    if (no_contacted == '') {
      telli('Please Dial Before Submiting Calltrack !');
      return;
    }

    if (lastagree == 1) {
      $('#remis').slideUp('fast');
      $('#subremis').slideUp('fast');
      $('#subunpresent').slideUp('fast');
      $('#multi_offer').show('fast');

    } else {
      //$('#subremis,#multi_subremis').fadeIn('slow');
      $('#subremis').fadeIn('slow');
      $('#subunpresent').fadeIn('slow');
      $('#remis').slideUp('fast');
    }

  }

  function go_present() {
    var no_contacted = jQuery('#no_contacted').val();

    if (no_contacted == '') {
      telli('Please Dial Before Submiting Calltrack !');
      return;
    }

    //$('#subremis,#multi_subremis').fadeIn('slow');
    $('#subremis').fadeIn('slow');
    $('#subpresent').fadeIn('slow');
    //$('#multi-offer').fadeIn('slow');
    $('#multi_offer').show('fast')
    $('#subunpresent').slideUp('fast');
    $('#remis').slideUp('fast');
  }

  function go_agree(call_code, $multioffers = '0', $multioffcard, $multiidx) {
    var id_prospect = jQuery('#id_prospect').val();
    //var id_product = jQuery('input[name=id_product]:checked').val();
    var id_product = $('#id_product').val();
    var id_calltrack = jQuery('#id_calltrack').val();
    var no_contacted = jQuery('#no_contacted').val();

    if (no_contacted == '') {
      telli('Please Dial Before Submitting !');
      return;
    }
    if ($multioffers != 0) {
      alert('"Form Agree" akan ditampilkan sebagai cross sale, mohon jangan salah membacakan script.');
    }
    //alert($multioffcard);
    var st_url = '<?= site_url() ?>/tsr/get_option_callcode/' + call_code + '/' + id_prospect + '/' + id_product + '/' + id_calltrack + '/' + no_contacted + '/' + $multioffers + '/' + $multioffcard + '/' + $multiidx;
    //alert(offcard);
    $('.det-right').fadeIn('slow');
    optionCodeCallTrack(st_url);
    $('#subremis').slideUp('slow');
    $('#connect').fadeIn();
  }

  function go_uncontacted() {
    var id_prospect = jQuery('#id_prospect').val();
    //var id_product = jQuery('input[name=id_product]:checked').val();
    var id_product = $('#id_product').val();
    //alert(id_product);
    var id_calltrack = jQuery('#id_calltrack').val();
    var no_contacted = jQuery('#no_contacted').val();

    if (no_contacted == '') {
      telli('Please Dial Before Submiting Calltrack !');
      return;
    }
    $('#remis').slideUp('fast');
    $('.det-right').fadeIn('slow');
    $('#multi_offer').slideUp('fast');
    var st_url = '<?= site_url() ?>/tsr/get_option_callcode/2/' + id_prospect + '/' + id_product + '/' + id_calltrack + '/' + no_contacted;
    //alert(st_url);
    optionCodeCallTrack(st_url);
    $('#connect').fadeIn();

  }

  function go_noteligible() {
    var msg = '<p>Nasabah akan diskip dan dinyatakan tidak eligible, yakin ?</p>';
    var url = "<?= site_url() ?>tsr/set_noteligible";
    new Boxy.confirm(msg, function() {
      upd_noteligible();
    });

    function upd_noteligible() {
      jQuery.post(url, {
          username: jQuery('#username').val(),
          id_user: jQuery('#id_user').val(),
          id_spv: jQuery('#id_spv').val(),
          id_tsm: jQuery('#id_tsm').val(),
          id_prospect: jQuery('#id_prospect').val(),
          id_product: jQuery('#id_product').val(),
          id_campaign: jQuery('#id_campaign_hid').val(),
          id_calltrack: jQuery('#id_calltrack').val(),
          id_callcode: jQuery('#id_callcode').val(),
          call_attempt: jQuery('#call_attempt').val(),
          remark: jQuery('#remark').val(),
          post: true
        },
        function(redirect) {
          location.href = redirect;
        });
    }
  }

  function go_waitingktp() {
    var msg = '<p>Nasabah Akan Waiting KTP, yakin ?</p>';
    var url = "<?= site_url() ?>tsr/set_waitingktp";
    new Boxy.confirm(msg, function() {
      upd_waitingktp();
    });

    function upd_waitingktp() {
      jQuery.post(url, {
          username: jQuery('#username').val(),
          id_user: jQuery('#id_user').val(),
          id_spv: jQuery('#id_spv').val(),
          id_tsm: jQuery('#id_tsm').val(),
          id_prospect: jQuery('#id_prospect').val(),
          id_product: jQuery('#id_product').val(),
          id_campaign: jQuery('#id_campaign_hid').val(),
          id_calltrack: jQuery('#id_calltrack').val(),
          id_callcode: jQuery('#id_callcode').val(),
          call_attempt: jQuery('#call_attempt').val(),
          remark: jQuery('#remark').val(),
          post: true
        },
        function(redirect) {
          location.href = redirect;
        });
    }
  }

  function show_uncontacted() {
    var id_prospect = jQuery('#id_prospect').val();
    var id_product = $('#id_product').val();
    var id_calltrack = jQuery('#id_calltrack').val();
    var no_contacted = jQuery('#no_contacted').val();

    if (no_contacted == '') {
      telli('Please Dial Before Submiting Calltrack !');
      return;
    }

    $('#remis').hide();
    $('#subremis').hide();
    $('#multi_offer').slideUp('fast');
    $('.det-right').fadeIn('fast');
    var st_url = '<?= site_url() ?>/tsr/get_option_callcode/3/' + id_prospect + '/' + id_product + '/' + id_calltrack + '/' + no_contacted;
    optionCodeCallTrack(st_url);
  }

  function sipCallblock(url, value, id_prospect, id_product, no_contacted) {

    //alert(url);
    //return;
    /*
              //if(id_product!=""){
               var st_url = '<h2> Calling, ' + no_contacted + ' Please wait...</h2><br/><h2><a href="#" onclick="Boxy.get(this).hide(); return false">' +
                '<a href="javascript:void(0)"  class="hide_box"  onclick="optionCodeCallTrack(\'<?= site_url() ?>/tsr/get_option_callcode/10/'+id_prospect+'/'+id_product+
                '/'+jQuery('#id_calltrack').val()+'/'+no_contacted+'\')" >CONTACTED </a> | <a href="javascript:void(0)" class="hide_box" ' +
                ' onclick="optionCodeCallTrack(\'<?= site_url() ?>/tsr/get_option_callcode/2/'+
                id_prospect+'/'+id_product+'/'+jQuery('#id_calltrack').val()+'/'+no_contacted+'\')" >UNCONTACTED</a></h2>' +
                '<br/><a href="#" onclick="Boxy.get(this).hide(); return false">[X] Close!</a>';

                alert(st_url);
                return;

                loading(st_url);

                jQuery.get(url,{
                }, function(html) {
                    uiPopUp(html);
                });
            //}else{
                //alert('Silahkan pilih product dahulu SCB');
            //}
    */
    //popup
    $('.dial-home').hide();
    $('.dial-office').hide();
    $('.dial-mobile').hide();
    $('#redialTrigger').fadeIn();
    $('#redialBox').fadeIn();
    $('#lastcall_url').val(url);

    call_IP(url);
    return;

  }

  function goRedial() {
    var url = $('#lastcall_url').val();
    call_IP(url);
  }

  function call_IP(url) {
    var myWindow = window.open(url, 'Dialup', 'width=10,height=10');
    setTimeout(function() {
      myWindow.close();
    }, 250);
  }

  function sipCallfirst(url, value, cnt, value2) {
    doSipCallfirst(url, value);

    function doSipCallfirst(url, value) {
      if (value != "") {
        var no_contacted = value;
        var val = [];
        $(':checkbox:checked').each(function(i) {
          val[i] = $(this).val();
        });

        var st_id_product = val.join("|").toString();
        // console.log(url);
        jQuery.post(url, {
            no_contacted: value,
            id_notelp: value2,
            username: jQuery('#username').val(),
            id_user: jQuery('#id_user').val(),
            id_spv: jQuery('#id_spv').val(),
            id_tsm: jQuery('#id_tsm').val(),
            id_prospect: jQuery('#id_prospect').val(),
            // id_product : st_id_product,
            id_product: jQuery('#id_product').val(),
            id_campaign: jQuery('#id_campaign_hid').val(),
            id_calltrack: jQuery('#id_calltrack').val(),
            id_callcode: jQuery('#id_callcode').val(),
            call_attempt: jQuery('#call_attempt').val(),
            remark: jQuery('#remark').val(),
            //id_product :  jQuery('input:radio[name=id_product]:checked').val(),
            post: true
          },
          function(html) {
            jQuery.unblockUI();
            telli(html);
          });
      }
    }

  }

  function uiPopUps(html) {
    //notif.hide();
    jQuery.blockUI({
      message: html,
      css: {
        padding: 0,
        margin: 0,
        width: '885px',
        top: '5%',
        left: ($(window).width() - 885) / 2 + 'px',
        textAlign: 'center',
        color: '#000',
        border: '7px solid #000',
        backgroundColor: '#48B8F3',
        '-webkit-border-radius': '10px',
        '-moz-border-radius': '10px',
        cursor: 'default'
      },
      baseZ: 1500,
      showOverlay: false,
      constrainTabKey: false,
      focusInput: false,
      onUnblock: null,
    });

    jQuery('#close').click(function() {
      jQuery.unblockUI();
      //notif.hide();
      return false;
    });
  }
</script>
<?php
function hide_5digit_phone($val)
{
  $val = trim($val);
  //  $temp = substr($val,0,strlen($val)-5) . 'xxxxx'; //untuk danamon dihide seluruhnya
  $temp = 'XXXXX';
  return $temp;
}
?>

<input type="hidden" id="message_popup" name="message_popup" value="0" />

<div class="martin">
  <div class="lmartin" style="float:left;padding: 20px 0px 0px 10px;width:240px;" onclick="javascript:location.href = '<?= site_url(); ?>listing/priority/'">
    <img src="<?= base_url() . 'component/images/priority.gif' ?>" />
    <span style="margin-left:10px;">Kamu memiliki <span id="priority_count">0</span> data prioritas.</span>
  </div>
  <div class="rmartin">
    <a onclick="hidepinfo();">[ X ]</a>
  </div>
</div>

<div class="martin2nd">
  <div class="lmartin2nd" style="float:left;padding: 20px 0px 0px 10px;width:240px;" onclick="javascript:location.href = '<?= site_url(); ?>listing/agree/'">
    <img src="<?= base_url() . 'component/images/priority.gif' ?>" />
    <span style="margin-left:10px;">Kamu memiliki <span id="sendback_count">0</span> data sendback.</span>
  </div>
  <div class="rmartin2nd">
    <a onclick="hidepinfo2nd();">[ X ]</a>
  </div>
</div>

<div class="tele">
  <div class="info">

    <?php if ($this->uri->segment(3) == '') : ?>
      <h1>Welcome, <?php echo @$_SESSION["fullname"] ?>!</h1>
      <p>Please select campaign to continue</p>
      <select name="id_campaign" id="id_campaign">
        <option value="">----</option>
        <?php if (!empty($list_campaign)) : ?>
          <?php foreach ($list_campaign as $row) : ?>
            <option <?php echo $row["id_campaign"] == $this->uri->segment(3) ? 'selected="true"' : '' ?> value="<?php echo $row["id_campaign"] ?>"><?php echo $row["name"] ?></option>
          <?php endforeach; ?>
        <?php endif ?>
      </select>
      <input type="submit" class="update" onclick="location.href='<?php echo site_url() ?>/tsr/main/'+jQuery('#id_campaign').val()" value="Submit" />
    <?php else : ?>
      <!--
           <strong>
            <?php if ($this->uri->segment(4) != '') : ?>
                <a href="#" onclick="ajaxBox('Add New Phone','<?php echo site_url() ?>/tsr/notelp/<?php echo $this->uri->segment(4) ?>')" class="new-phone">+New Phone</a>
            <?php endif; ?>
           </strong>
        -->
      <div class="head">

        <h1>Customer Data</h1>
        <span class="search"><input type="text" name="keyword" id="keyword" class="txt-search" list="cities" /> <input type="submit" value="" class="btn-search" onclick="ajaxBox('Browse Prospects','<?php echo site_url() ?>/tsr/list_prospect/<?php echo $this->uri->segment(3) ?>/'+jQuery('#keyword').val())" /></span>
        <input type="button" class="btn-browse" value="Browse" onclick="ajaxBox('Browse Prospects','<?php echo site_url() ?>/tsr/list_prospect/<?php echo $this->uri->segment(3) ?>')" />
      </div>
      <?php if ($this->uri->segment(4) != '') : ?>
        <div class="det-left" style="height: 775px !important;">
          <input type="button" class="btn-contacted" id="btn-additional" value="Cust Info" onclick="showAdditionalInfo()" />
          <!-- <input type="button" class="btn-contacted" id="btn-additional" value="Cust Info" onclick="showAdditionalInfo()" /> -->
          <input type="button" class="btn-contacted" id="btn-verf" value="Verifikasi" onclick="loadFormVerifikasi()" /> &nbsp;
          <?Php if ($prospect['campaign_product'] == '54') { ?>
            <input type="button" class="btn-contacted" id="btn-verf" value="Check Zone" onclick="zipcodelist('show')" />
          <?php } ?>
          <dl>
            <dt class="det-tele">


              <table border="1" cellpadding="1" cellspacing="0" style="width:700px;">
                <tr>
                  <td width="125">#No Case</td>
                  <td>:</td>
                  <td><?php echo 'CASE' . str_pad($prospect['id_prospect'], 8, 0, STR_PAD_LEFT) ?> / <?= $prospect['cnum']; ?> </td>
                </tr>
                <tr>
                  <td width="125">Campaign</td>
                  <td>:</td>
                  <td><?php echo $prospect['name']; ?></td>
                </tr>
                <!-- BUG:         <tr>
         <td width="125">Timezone</td>  
         <td>:</td>
         <td><?php echo $prospect['timezone'] == '' ? 'No Data' : $prospect['timezone']; ?></td>
        </tr> -->
                <?php if ($prospect['campaign_product'] != '39') { ?>
                  <tr>
                    <td width="125">Product</td>
                    <td>:</td>
                    <td><?php echo $prospect['product_name'] ?> - <?php echo $miscModel->get_producttype($prospect["campaign_type"]);  ?></td>
                  </tr>
                <?php } ?>
                <tr class="<?php echo COUNT(@$offers) <= 0 ? 'hide' : ""; ?>">
                  <td width="125">Multi Product</td>
                  <td>:</td>
                  <td>
                    <?php foreach ($offers as $offer) : ?>
                      <?php
                      //var_dump($offers);
                      if ($offer['xsell_cardxsell'] == '') {
                        $offer = array(); //no multiproduct
                        echo '-No Data-';
                      } else {
                        $offer = json_decode($offer['xsell_cardxsell']);
                        foreach ($offer as $val) {
                          $data_offer[] = $val;
                        }
                        echo implode(', ', $data_offer);
                      }
                      ?>
                    <?php endforeach; ?>
                  </td>
                </tr>


                <tr>
                  <td width="125">Nama</td>
                  <td>:</td>
                  <td><strong style="font-size: 1.3em;"><?php echo $prospect['fullname'] ?> <?= $prospect['is_priority'] == '1' ? '<font class="blink_me" color="CORAL" title="No callblocking rule to this account">(PRIORITIZE CUSTOMER)</font>' : ''; ?></strong></td>
                </tr>
                <!--        
        <tr>
         <td width="125">Umur</td>
         <td>:</td>
         <td><?php echo $prospect['cust_age'] ?></td>
        </tr> 
-->
                <!--        <tr style="display:none">
         <td width="125">Nama Ibu Kandung</td>
         <td>:</td>
         <td>
          <?php
          //echo $prospect['maiden_name'];
          ?>
          </td>
        </tr>
-->
                <!--
        <tr>
         <td width="125">Nama Perusahaan</td>
         <td>:</td>
         <td><?php echo $prospect['company_name'] ?></td>
        </tr> 
-->


                <tr class="<?= empty($prospect['gender']) ? 'hide' : ''; ?>">
                  <td>Gender</td>
                  <td>:</td>
                  <td><?php echo $prospect['gender'] ?>
                    <?php
                    switch (strtolower($prospect['gender'])) {
                      case 'perempuan':
                        echo '<img src="' . base_url() . '/component/images/female.png" alt="Female" width="16" height="16" />';
                        break;
                      case 'wanita':
                        echo '<img src="' . base_url() . '/component/images/female.png" alt="Female" width="16" height="16" />';
                        break;
                      case 'female':
                        echo '<img src="' . base_url() . '/component/images/female.png" alt="Female" width="16" height="16" />';
                        break;
                      case 'f':
                        echo '<img src="' . base_url() . '/component/images/female.png" alt="Female" width="16" height="16" />';
                        break;
                      case '0':
                        echo '<img src="' . base_url() . '/component/images/female.png" alt="Female" width="16" height="16" />';
                        break;
                      case 'laki':
                        echo '<img src="' . base_url() . '/component/images/male.png" alt="Male" width="16" height="16" />';
                        break;
                      case 'lakilaki':
                        echo '<img src="' . base_url() . '/component/images/male.png" alt="Male" width="16" height="16" />';
                        break;
                      case 'laki-laki':
                        echo '<img src="' . base_url() . '/component/images/male.png" alt="Male" width="16" height="16" />';
                        break;
                      case 'm':
                        echo '<img src="' . base_url() . '/component/images/male.png" alt="Male" width="16" height="16" />';
                        break;
                      case '1':
                        echo '<img src="' . base_url() . '/component/images/male.png" alt="Male" width="16" height="16" />';
                        break;
                      case 'pria':
                        echo '<img src="' . base_url() . '/component/images/male.png" alt="Male" width="16" height="16" />';
                        break;

                      default:
                        echo '';
                        break;
                    }
                    ?>
                  </td>
                </tr>

                <?php if ($prospect['campaign_product'] == '59') : ?>
                  <tr class="<?= empty($prospect['available_credit']) ? 'hide' : ''; ?>">
                    <td><strong>Available Limit</strong></td>
                    <td>:</td>
                    <td>
                      <strong style="font-size: 1.3em;">
                        <p style="width:500px; word-wrap: break-word;"><?php echo number_format($prospect['available_credit'], 0) ?></p>
                      </strong>
                    </td>
                  </tr>
                <?php endif; ?>
                <!--    
        <tr>
         <td>Segment</td>
         <td>:</td>
         <td>
          <?php
          echo $prospect['segment1'];
          echo !empty($prospect['segment2']) ? ' / ' . $prospect['segment2'] : '';
          echo !empty($prospect['segment3']) ? ' / ' . $prospect['segment3'] : '';
          ?>
         </td>
        </tr>
-->
                <!--        
        <tr>
         <td>Tenor</td>
         <td>:</td>
         <td><?php echo $prospect['tenor'] ?></td>
        </tr>
-->
                <?php if ($prospect['campaign_product'] == '44') : ?>
                  <tr class="<?= empty($prospect['status2']) ? 'hide' : ''; ?>">
                    <td><strong>Last_taken</strong></td>
                    <td>:</td>
                    <td>
                      <strong>
                        <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['status2'] ?></p>
                      </strong>
                    </td>
                  </tr>
                <?php endif; ?>
                <?php if($prospect['campaign_product'] == '57' && in_array("COP", json_decode($offers[0]['xsell_cardxsell']))){?>
                    <tr class="<?= empty($prospect['datainfo']) ? 'hide' : ''; ?>">
                      <td>Data Info COP</td>
                      <td>:</td>
                      <td>
                        <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['datainfo'] ?></p>
                      </td>
                    </tr>
                <?php } ?>
                <?php if ($prospect['campaign_product'] != '57' && $prospect['campaign_type'] != '8' && $prospect['campaign_product'] != '59') : ?>
                  <tr class="<?= empty($prospect['datainfo']) ? 'hide' : ''; ?>">
                    <td>Data Info</td>
                    <td>:</td>
                    <td>
                      <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['datainfo'] ?></p>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['campaign_type'] == '8') : ?>
                  <tr class="<?= empty($prospect['plafon24']) ? 'hide' : ''; ?>">
                    <td>Last Taken</td>
                    <td>:</td>
                    <td>
                      <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['plafon24'] ?></p>
                    </td>
                  </tr>
                  <tr class="<?= empty($prospect['plafon36']) ? 'hide' : ''; ?>">
                    <td>Data Total Tagihan</td>
                    <td>:</td>
                    <td>
                      <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['plafon36'] ?></p>
                    </td>
                  </tr>
                  <tr class="<?= empty($prospect['datainfo']) ? 'hide' : ''; ?>">
                    <td>Data Eligible FOP STMT</td>
                    <td>:</td>
                    <td>
                      <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['datainfo'] ?></p>
                      <?php if (!empty($refreshbag)) { ?>
                        <span class="refresh" title="Last Refresh: <?= $refreshbagfop['period'] ?>"><?php echo $refreshbag['data_info'] ?></span>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr class="<?= empty($prospect['available_credit']) ? 'hide' : ''; ?>">
                    <td>Available Credit Limit</td>
                    <td>:</td>
                    <td>
                      <p style="width:500px; word-wrap: break-word;">Rp. <?php echo number_format($prospect['available_credit'], 0) ?></p>
                      <?php if (!empty($refreshbag)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbagfop['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbag['available_credit']); ?></span>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr class="<?= empty($prospect['plafon12']) ? 'hide' : ''; ?>">
                    <td>Payment Segment</td>
                    <td>:</td>
                    <td>
                      <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['plafon12'] ?></p>
                    </td>
                  </tr>

                  <tr class="<?= empty($prospect['status']) ? 'hide' : ''; ?>">
                    <td>Status</td>
                    <td>:</td>
                    <td><?php echo $prospect['status'] ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['cycle']) ? 'hide' : ''; ?>">
                    <td>Cycle</td>
                    <td>:</td>
                    <td><?php echo $prospect['cycle']; ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['interest']) ? 'hide' : ''; ?>">
                    <td>Due Date</td>
                    <td>:</td>
                    <td>
                      <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['interest'] ?></p>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['campaign_product'] != '32' && $prospect['campaign_product'] != '54' && $prospect['campaign_product'] != '59' && $prospect['campaign_product'] != '39') : ?>
                  <tr class="<?= empty($prospect['home_address1']) ? 'hide' : ''; ?>">
                    <td>Alamat</td>
                    <td>:</td>
                    <td>
                      <p style="width:500px;"><?php echo implode(' ', array($prospect['home_address1'], $prospect['home_address2'], $prospect['home_city'], $prospect['home_zipcode'])); ?></p>
                    </td>
                  </tr>
                <?php endif; ?>

                <!-- TODO:         
        <tr>
         <td>Alamat 2</td>
         <td>:</td>
         <td><p style="width:500px;"><?php echo $prospect['home_address2']; ?></p></td>
        </tr> 
 -->
                <tr class="<?= empty($prospect['custom3']) || $prospect['campaign_product'] == 54 ? 'hide' : ''; ?>">
                  <td>Upgrade Card</td>
                  <td>:</td>
                  <td><strong style="font-size: 1.3em;">From <font color="CORAL" title="No callblocking rule to this account"><?php echo $prospect['card_type']; ?></font> To <font color="CORAL" title="No callblocking rule to this account"><?php echo $prospect['custom3']; ?></font></strong></td>
                  <!--<td><p><?php echo $prospect['card_type']; ?> To <?php echo $prospect['custom3']; ?></p></td>-->
                </tr>
                <tr class="<?= empty($prospect['custom2']) || $prospect['campaign_product'] == 54 ? 'hide' : ''; ?>">
                  <td>Info</td>
                  <td>:</td>
                  <td><strong><?php echo 'Rp.' . price_format($prospect['custom2']); ?></strong>
                    <!-- BUG: <strong style="font-size: 1.3em;">From <font color="CORAL" title="No callblocking rule to this account"><?php echo $prospect['card_type']; ?></font> To <font color="CORAL" title="No callblocking rule to this account"><?php echo $prospect['custom3']; ?></font></strong> -->
                  </td>
                  <!--<td><p><?php echo $prospect['card_type']; ?> To <?php echo $prospect['custom2']; ?></p></td>-->
                </tr>

                <?php if ($prospect['campaign_product'] != '39' || $prospect['campaign_product'] == '47') : //FlexiPay & Cash On Phone 
                ?>
                  <tr class="<?= empty($prospect['company_name']) ? 'hide' : ''; ?>">
                    <td>Nama Perusahaan</td>
                    <td>:</td>
                    <td><?php echo $prospect['company_name'] ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['main_branch_name']) ? 'hide' : ''; ?>">
                    <td>Branch Name</td>
                    <td>:</td>
                    <td><?php echo $prospect['main_branch_name']; ?></td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['campaign_product'] == '44' || ($prospect['campaign_product'] == '46' || in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) || $prospect['campaign_product'] == '47' || $prospect['campaign_product'] == '48' || $prospect['campaign_product'] == '50' || $prospect['campaign_product'] == '52' || $prospect['campaign_product'] == '53' || $prospect['campaign_product'] == '49' || $prospect['campaign_product'] == '40' || $prospect['campaign_product'] == '41' || $prospect['campaign_product'] == '42') : //FlexiPay & Cash On Phone & Supplement & Aktivasi 
                ?>
                  <?php if ($prospect['multiproduct'] == '1') : ?>
                    <?php if ($prospect['campaign_product'] == '57' && in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) { ?>
                      <tr class="<?= empty($prospect['status']) ? 'hide' : ''; ?>">
                        <td>Status COP</td>
                        <td>:</td>
                        <td><?php echo $prospect['status'] ?></td>
                      </tr>
                    <?php } else if ($prospect['campaign_product'] == '46') { ?>
                      <tr class="<?= empty($prospect['status']) ? 'hide' : ''; ?>">
                        <td>Status COP</td>
                        <td>:</td>
                        <td><?php echo $prospect['status'] ?></td>
                      </tr>
                      <tr class="<?= empty($prospect['status2'])? 'hide' : ''; ?>">
                        <td>Status FOP</td>
                        <td>:</td>
                        <td><?php echo $prospect['status2'] ?></td>
                      </tr>
                    <?php } elseif ($prospect['campaign_product'] == '44') { ?>
                      <tr class="<?= empty($prospect['status']) ? 'hide' : ''; ?>">
                        <td>Status FOP</td>
                        <td>:</td>
                        <td><?php echo $prospect['status'] ?></td>
                      </tr>
                      <!--<tr class="<?= empty($prospect['status2']) ? 'hide' : ''; ?>">
                        <td>Status COP</td>
                        <td>:</td>
                        <td><?php echo $prospect['status2'] ?></td>
                      </tr>-->
                    <?php } elseif ($prospect['campaign_product'] == '32') { ?>
                      <tr class="<?= empty($prospect['status']) ? 'hide' : ''; ?>">
                        <td>Status Personal Loan</td>
                        <td>:</td>
                        <td><?php echo $prospect['status'] ?></td>
                      </tr>
                      <tr class="<?= empty($prospect['status2']) ? 'hide' : ''; ?>">
                        <td>Status FOP</td>
                        <td>:</td>
                        <td><?php echo $prospect['status2'] ?></td>
                      </tr>
                    <?php } ?>
                  <?php else : ?>
                    <tr class="<?= empty($prospect['status']) ? 'hide' : ''; ?>">
                      <td>Status</td>
                      <td>:</td>
                      <td><?php echo $prospect['status'] ?></td>
                    </tr>
                  <?php endif; ?>
                  
                  <tr class="<?= empty($prospect['code_tele']) ? 'hide' : ''; ?>">
                    <td>Code Tele</td>
                    <td>:</td>
                    <td><?php echo $prospect['code_tele'] ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['email']) ? 'hide' : ''; ?>">
                    <td>Email</td>
                    <td>:</td>
                    <td class="lower"><?php echo $prospect['email'] ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['card_number_basic']) ? 'hide' : 'hide'; ?>">
                    <td>Card Basic</td>
                    <td>:</td>
                    <td><?php echo $prospect['card_number_basic']; ?> / <?php echo $prospect['card_type']; ?> / Cycle: <?php echo $prospect['cycle']; ?></td>
                  </tr>
                  
                  <tr class="<?= empty($prospect['cycle']) ? 'hide' : ''; ?>">
                    <td>Cycle <?= !in_array("CPIL", json_decode($offers[0]['xsell_cardxsell'])) ? '' : 'COP' ?></td>
                    <td>:</td>
                    <td><?php echo $prospect['cycle']; ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['card_type']) ? 'hide' : ''; ?>">
                    <td>Card Type <?= !in_array("CPIL", json_decode($offers[0]['xsell_cardxsell'])) ? '' : 'COP' ?></td>
                    <td>:</td>
                    <td><?php echo $prospect['card_type'] ?></td>
                  </tr>
                  <!--
        <tr class="<?= empty($prospect['available_credit']) ? 'hide' : ''; ?>">
         <td>Available Credit</td>
         <td>:</td>
         <td><?php echo 'Rp.' . price_format($prospect['available_credit']); ?></td>
        </tr>
-->
                <?php endif; ?>

                <?php if ($prospect['campaign_product'] == '54') : ?>
                  <tr class="<?= empty($prospect['email']) ? 'hide' : ''; ?>">
                    <td>Email</td>
                    <td>:</td>
                    <td class="lower"><?php echo $prospect['email'] ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['cif_no']) ? 'hide' : ''; ?>">
                    <td>Cif No</td>
                    <td>:</td>
                    <td class="lower"><?php echo $prospect['cif_no'] ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['card_type']) ? 'hide' : ''; ?>">
                    <td>Card Type</td>
                    <td>:</td>
                    <td class="lower"><?php echo $prospect['card_type'] ?></td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['campaign_product'] == '46' || in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) : //Cash On Phone 
                ?>
                  <tr style="display:none;">
                    <td>Eligible 0%</td>
                    <td>:</td>
                    <td><?php echo $prospect['custom1'] == 'R-0' ? 'Yes' : 'No' ?></td>
                  </tr>
                  <tr class="<?= empty($prospect['max_loan']) ? 'hide' : ''; ?>">
                    <td>Available Limit COP</td>
                    <td>:</td>
                    <td><?php echo 'Rp. ' . price_format($prospect['max_loan']); ?>
                      <?php if (!empty($refreshbagcop)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbagcop['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbagcop['max_loan']); ?></span>
                      <?php }else if (!empty($refreshbag) && !in_array("CPIL", json_decode($offers[0]['xsell_cardxsell']))) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbag['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbag['max_loan']); ?></span>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Loan 1</td>
                    <td>:</td>
                    <td><?php echo 'Rp. ' . price_format($prospect['loan1']); ?>
                      <?php if (!empty($refreshbagcop)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbagcop['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbagcop['loan1']); ?></span>
                      <?php }else if (!empty($refreshbag) && !in_array("CPIL", json_decode($offers[0]['xsell_cardxsell']))) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbag['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbag['loan1']); ?></span>
                    </td>
                  <?php } ?>
                  </tr>
                  <tr>
                    <td>Loan 2</td>
                    <td>:</td>
                    <td><?php echo 'Rp. ' . price_format($prospect['loan2']); ?>
                      <?php if (!empty($refreshbagcop)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbagcop['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbagcop['loan2']); ?></span>
                      <?php }else if (!empty($refreshbag) && !in_array("CPIL", json_decode($offers[0]['xsell_cardxsell']))) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbag['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbag['loan2']); ?></span>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Loan 3</td>
                    <td>:</td>
                    <td><?php echo 'Rp. ' . price_format($prospect['loan3']); ?>
                      <?php if (!empty($refreshbagcop)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbagcop['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbagcop['loan3']); ?></span>
                      <?php }else if (!empty($refreshbag) && !in_array("CPIL", json_decode($offers[0]['xsell_cardxsell']))) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbag['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbag['loan3']); ?></span>
                      <?php } ?>
                    </td>

                  </tr>
                <?php endif; ?>

                <?php if ($prospect['campaign_product'] == '32') : //PL 
                ?>
                  <tr class="hide">
                    <td>Card Number</td>
                    <td>:</td>
                    <td>

                    </td>
                  </tr>

                  <tr>
                    <td>Card Type</td>
                    <td>:</td>
                    <td>
                      <?php echo strtoupper($prospect['card_type']); ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Campaign Type</td>
                    <td>:</td>
                    <td>
                      <?php echo strtoupper($prospect['merchant_type']); ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Final Limit 12 reg NPWP</td>
                    <td>:</td>
                    <td>
                      <?php echo 'Rp. ' . price_format($prospect['limit12_pl']); ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Final Limit 24 reg NPWP</td>
                    <td>:</td>
                    <td>
                      <?php echo 'Rp. ' . price_format($prospect['limit24_pl']); ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Final Limit 36 reg NPWP</td>
                    <td>:</td>
                    <td>
                      <?php echo 'Rp. ' . price_format($prospect['limit36_pl']); ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Max PL Offer:</td>
                    <td>:</td>
                    <td>
                      <table style="width: 500px;">
                        <tr>
                          <th>Tenor 12</th>
                          <th>Tenor 24</th>
                          <th>Tenor 36</th>
                          <!-- <th>Tenor 48</th> -->
                        </tr>
                        <tr class="a">
                          <td><?php echo 'Rp. ' . price_format($prospect['plafon12']); ?></td>
                          <td><?php echo 'Rp. ' . price_format($prospect['plafon24']); ?></td>
                          <td><?php echo 'Rp. ' . price_format($prospect['plafon36']); ?></td>
                          <!-- <td><?php echo 'Rp. ' . price_format($prospect['plafon48']); ?></td> -->
                        </tr>
                      </table>
                    </td>
                  </tr>

                <?php endif; ?>


                <?php if ($prospect['campaign_product'] == '57') : ?>
                  <?php if (in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) :  ?>
                  <tr class="<?= empty($prospect['datainfo_xsell']) ? 'hide' : ''; ?>">
                    <td>Last Taken</td>
                    <td>:</td>
                    <td>
                      <strong style="font-size: 1.3em;"><?php echo $prospect['datainfo_xsell'] ?></strong>
                      <!-- <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['datainfo'] ?></p> -->
                    </td>
                  </tr>
                  <?php else : ?>
                    <tr class="<?= empty($prospect['datainfo']) ? 'hide' : ''; ?>">
                      <td>Last Taken</td>
                      <td>:</td>
                      <td>
                        <strong style="font-size: 1.3em;"><?php echo $prospect['datainfo'] ?></strong>
                        <!-- <p style="width:500px; word-wrap: break-word;"><?php echo $prospect['datainfo'] ?></p> -->
                      </td>
                    </tr>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($prospect['campaign_product'] == '57') : //Cash PLUS 
                ?>
                  <tr class='hide'>
                    <!-- <tr class="<?= empty($prospect['status2']) ? 'hide' : ''; ?>"> -->
                    <td>Rate</td>
                    <td>:</td>
                    <td><?php echo $prospect['status2'] ?></td>
                  </tr>
                  <?php if (in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) { ?>
                  <tr>
                    <!-- <tr class="<?= empty($prospect['status_rate_cpil']) ? 'hide' : ''; ?>"> -->
                    <td>Rate</td>
                    <td>:</td>
                    <td><?php echo $prospect['status_rate_cpil'] ?></td>
                  </tr>
                  
                  <tr class="<?= empty($prospect['card_exp']) ? 'hide' : ''; ?>">
                    <td>Cycle</td>
                    <td>:</td>
                    <td><?php echo $prospect['card_exp']; ?></td>
                  </tr>
                  <?php } else{ ?>
                    <tr>
                      <!-- <tr class="<?= empty($prospect['status2']) ? 'hide' : ''; ?>"> -->
                      <td>Rate</td>
                      <td>:</td>
                      <td><?php echo $prospect['status2'] ?></td>
                    </tr>
                    
                    <tr class="<?= empty($prospect['cycle']) ? 'hide' : ''; ?>">
                      <td>Cycle</td>
                      <td>:</td>
                      <td><?php echo $prospect['cycle']; ?></td>
                    </tr>
                  <?php } ?>

                  <tr style="display:none;"> 
                    <td>Eligible 0%</td>
                    <td>:</td>
                    <td><?php echo $prospect['custom1'] == 'R-0' ? 'Yes' : 'No' ?></td>
                  </tr>
                  <tr class="hide">
                    <!-- <tr class="<?= empty($prospect['code_tele']) ? 'hide' : ''; ?>"> -->
                    <td>Code Tele</td>
                    <td>:</td>
                    <td><?php echo $prospect['code_tele'] ?></td>
                  </tr>
                  <tr class="hide">
                    <!-- <tr class="<?= empty($prospect['card_type']) ? 'hide' : ''; ?>"> -->
                    <td>Card Type</td>
                    <td>:</td>
                    <td><?php echo $prospect['card_type'] ?></td>
                  </tr>

                  <tr class="<?= empty($prospect['creditlimit']) ? 'hide' : ''; ?>">
                    <td>Credit Limit</td>
                    <td>:</td>
                    <td><?php echo 'Rp. ' . price_format($prospect['creditlimit']); ?>
                      <?php if (!empty($refreshbag)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbag['period'] ?>"><?php echo 'Rp. ' . (($refreshbag['creditlimit'] == '0') ?  $refreshbag['creditlimit'] : price_format($refreshbag['creditlimit'])); ?></span>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Available Limit</td>
                    <td>:</td>
                    <td><?php echo 'Rp. ' . price_format($prospect['available_credit']); ?>
                      <?php if (!empty($refreshbag)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbag['period'] ?>"><?php echo 'Rp. ' . (($refreshbag['available_credit'] == '0') ?  $refreshbag['available_credit'] : price_format($refreshbag['available_credit'])); ?></span>
                      <?php } ?>
                  </td>
                  </tr>
                  <?php
                    if (in_array("COP", json_decode($offers[0]['xsell_cardxsell']))){
                  ?>
                  <tr style="font-weight: 600;">
                    <td>Total Available Limit CPIL + COP</td>
                    <td>:</td>
                    <?php
                    $totallimitcpilcop = 0;
                    $totallimitcpilcop = $totallimitcpilcop + (!empty($refreshbag) ? $refreshbag['available_credit'] : $prospect['available_credit']);
                    $totallimitcpilcop = $totallimitcpilcop + (!empty($refreshbagcop) ? $refreshbagcop['max_loan'] : $prospect['max_loan']);
                    $totaloricpilcop = $prospect['available_credit'] + $prospect['max_loan'];
                    ?>
                    <td style="color: #d3b71a; font-size: 12px;"><?php echo 'Rp. ' . price_format($totaloricpilcop); ?>
                      <?php if (!empty($refreshbag) || !empty($refreshbagcop)) { ?>
                        <span style="color: #736f6f; font-weight: 100; font-size: 11px;">----</span> <span class="refresh" ><?php echo 'Rp. ' . price_format($totallimitcpilcop); ?></span>
                      <?php } ?>
                    </td>
                  </tr>

                  <?php } ?>
                  <tr class="hide">
                    <td>Loan 2</td>
                    <td>:</td>
                    <td><?php echo 'Rp. ' . price_format($prospect['loan2']); ?>
                      <?php if (!empty($refreshbag)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbag['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbag['loan2']); ?></span>
                      <?php } ?>
                    </td>
                  </tr>
                  <tr class="hide">
                    <td>Loan 3</td>
                    <td>:</td>
                    <td><?php echo 'Rp. ' . price_format($prospect['loan3']); ?>
                      <?php if (!empty($refreshbag)) { ?>
                        ---- <span class="refresh" title="Last Refresh: <?= $refreshbag['period'] ?>"><?php echo 'Rp. ' . price_format($refreshbag['loan3']); ?></span>
                      <?php } ?>
                    </td>

                  </tr>
                <?php endif; ?>

                <?php function clean_phone($phone)
                {

                  if (substr($phone, 0, 3) == '021') {
                    $phone = substr_replace($phone, '', 0, 3);
                    //$phone = $phone;
                  }
                  $phone = preg_replace('/[^0-9]/', '', $phone);
                  return $phone;
                }
                ?>

                <?php
                $totalCallBeforeToday = $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], '0');
                $totalCallToday = $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], '0');
                $totalCallThisMonth = $calltrackModel->count_totalDialledThisMonth($prospect['id_prospect'], '0');
                ?>


                <?php if ($prospect['is_recycle'] == '1') : ?>
                  <tr>
                    <td>Total Dialled</td>
                    <td>:</td>
                    <td>
                      <!-- <span style="font-size: 1.2em; font-weight:bold;"> <span title="Total dihubungi hari ini"><?php echo $totalCallToday; ?></span> / <span title="Total dihubungi sampai sebelum hari ini"><?php echo $totalCallBeforeToday; ?><span></span></span> -->
                      <table class="userlist" style="width: 250px;">
                        <tr>
                          <th>Call Today</th>
                          <th>Total Call <?= Date('F') ?></th>
                          <th>Max Call</th>
                        </tr>
                        <tr>
                          <td>0</td>
                          <td>0</td>
                          <td>0</td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_recycle'] != '1') : ?>
                  <tr>
                    <td>Total Dialled</td>
                    <td>:</td>
                    <td>
                      <!-- <span style="font-size: 1.2em; font-weight:bold;"> <span title="Total dihubungi hari ini"><?php echo $totalCallToday; ?></span> / <span title="Total dihubungi sampai sebelum hari ini"><?php echo $totalCallBeforeToday; ?><span></span></span> -->
                      <table class="userlist" style="width: 250px;">
                        <tr>
                          <th>Call Today</th>
                          <th>Total Call <?= Date('F') ?></th>
                          <th>Max Call</th>
                        </tr>
                        <tr>
                          <td><?= @$totalCallToday * 1; ?></td>
                          <td><?= @$totalCallThisMonth * 1; ?></td>
                          <td><?= $maxcall_enable ? $maxcall_permonth * 1 : 'Disabled'; ?> </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '1') : ?>
                  <tr>
                    <td>Dial Rumah</td>
                    <td>:</td>
                    <td>
                      <?php if ($prospect['home_phone1']) :
                        echo @hide_5digit_phone($prospect['home_phone1']);
                      ?>
                        &nbsp;
                        <a title="Telepon Rumah" href="#" class="dial-home" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['home_phone1']) ?>','<?= clean_phone($prospect['home_phone1']) ?>', '<?php echo $totalCallToday; ?>', '11'); return false;">Dial</a>
                      <?php endif ?>
                      (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['home_phone1'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['home_phone1'])); ?>)

                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '1') : ?>
                  <tr style="display:<?php echo strlen($prospect['home_phone2']) > 4 ? 'table-row' : 'none'; ?>">
                    <td>Dial Rumah 2</td>
                    <td>:</td>
                    <td>
                      <?php if ($prospect['home_phone2']) :
                        echo @hide_5digit_phone($prospect['home_phone2']);
                      ?>
                        &nbsp;
                        <a title="Telepon Rumah2" href="#" class="dial-home" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['home_phone2']) ?>','<?= clean_phone($prospect['home_phone2']) ?>', '<?php echo $totalCallToday; ?>', '12'); return false;">Dial</a>
                      <?php endif ?>
                      (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['home_phone2'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['home_phone2'])); ?>)

                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '1') : ?>
                  <tr>
                    <td>Dial Kantor</td>
                    <td>:</td>
                    <td>
                      <?php if ($prospect['office_phone1']) :
                        echo @hide_5digit_phone($prospect['office_phone1']);
                      ?>
                        &nbsp;
                        <a title="Telepon Kantor" href="#" class="dial-office" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['office_phone1']) ?>','<?= clean_phone($prospect['office_phone1']) ?>', '<?php echo $totalCallToday; ?>', '13'); return false;">Dial</a>
                        (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['office_phone1'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['office_phone1'])); ?>)
                      <?php endif ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '1') : ?>
                  <tr style="display:<?php echo strlen($prospect['office_phone2']) > 4 ? 'table-row' : 'none'; ?>">
                    <td>Dial Kantor 2</td>
                    <td>:</td>
                    <td>
                      <?php if ($prospect['office_phone2']) :
                        echo @hide_5digit_phone($prospect['office_phone2']);
                      ?>
                        &nbsp;
                        <a title="Telepon Kantor 2" href="#" class="dial-office" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['office_phone2']) ?>','<?= clean_phone($prospect['office_phone2']) ?>', '<?php echo $totalCallToday; ?>', '14'); return false;">Dial</a>
                        (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['office_phone2'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['office_phone2'])); ?>)
                      <?php endif ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '1') : ?>
                  <tr>
                    <td>Dial HP</td>
                    <td>:</td>
                    <td>
                      <?php
                      if ($prospect['hp1']) :
                        echo @hide_5digit_phone($prospect['hp1']);
                      ?>
                        &nbsp;
                        <a href="#" title="Hand Phone" class="dial-mobile" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['hp1']) ?>','<?= clean_phone($prospect['hp1']) ?>', '<?php echo $totalCallToday; ?>', '9'); return false;">Dial</a>
                        (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['hp1'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['hp1'])); ?>)
                      <?php endif ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '1') : ?>
                  <tr style="display:<?php echo strlen($prospect['hp2']) > 4 ? 'table-row' : 'none'; ?>">
                    <td>Dial HP 2</td>
                    <td>:</td>
                    <td>
                      <?php
                      if ($prospect['hp2']) :
                        echo @hide_5digit_phone($prospect['hp2']);
                      ?>
                        &nbsp;
                        <a href="#" title="Hand Phone 2" class="dial-mobile" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['hp2']) ?>','<?= clean_phone($prospect['hp2']) ?>', '<?php echo $totalCallToday; ?>', '10'); return false;">Dial</a>
                        (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['hp2'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['hp1'])); ?>)
                      <?php endif ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '0') : ?>
                  <tr>
                    <td>Dial Rumah</td>
                    <td>:</td>
                    <td>
                      <?php if ($prospect['home_phone1']) :
                        echo @hide_5digit_phone($prospect['home_phone1']);
                      ?>
                        &nbsp;
                        <?php if ($set_time_call == '1') : ?>
                          <a title="Telepon Rumah" href="#" class="dial-home" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['home_phone1']) ?>','<?= clean_phone($prospect['home_phone1']) ?>', '<?php echo $totalCallToday; ?>', '11'); return false;">Dial</a>
                        <?php endif ?>
                        (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['home_phone1'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['home_phone1'])); ?>)
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '0') : ?>
                  <tr style="display:<?php echo strlen($prospect['home_phone2']) > 4 ? 'table-row' : 'none'; ?>">
                    <td>Dial Rumah 2</td>
                    <td>:</td>
                    <td>
                      <?php if ($prospect['home_phone2']) :
                        echo @hide_5digit_phone($prospect['home_phone2']);
                      ?>
                        &nbsp;
                        <?php if ($set_time_call == '1') : ?>
                          <a title="Telepon Rumah2" href="#" class="dial-home" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['home_phone2']) ?>','<?= clean_phone($prospect['home_phone2']) ?>', '<?php echo $totalCallToday; ?>', '12'); return false;">Dial</a>
                        <?php endif ?>
                        (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['home_phone2'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['home_phone2'])); ?>)
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '0') : ?>
                  <tr>
                    <td>Dial Kantor</td>
                    <td>:</td>
                    <td>
                      <?php if ($prospect['office_phone1']) :
                        echo @hide_5digit_phone($prospect['office_phone1']);
                      ?>
                        &nbsp;
                        <?php if ($set_time_call == '1') : ?>
                          <a title="Telepon Kantor" href="#" class="dial-office" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['office_phone1']) ?>','<?= clean_phone($prospect['office_phone1']) ?>', '<?php echo $totalCallToday; ?>', '13'); return false;">Dial</a>
                          (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['office_phone1'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['office_phone1'])); ?>)
                        <?php endif ?>

                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '0') : ?>
                  <tr style="display:<?php echo strlen($prospect['office_phone2']) > 4 ? 'table-row' : 'none'; ?>">
                    <td>Dial Kantor 2</td>
                    <td>:</td>
                    <td>
                      <?php if ($prospect['office_phone2']) :
                        echo @hide_5digit_phone($prospect['office_phone2']);
                      ?>
                        &nbsp;
                        <?php if ($set_time_call == '1') : ?>
                          <a title="Telepon Kantor 2" href="#" class="dial-office" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['office_phone2']) ?>','<?= clean_phone($prospect['office_phone2']) ?>', '<?php echo $totalCallToday; ?>', '14'); return false;">Dial</a>
                          (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['office_phone2'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['office_phone2'])); ?>)
                        <?php endif ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '0') : ?>
                  <tr>
                    <td>Dial HP</td>
                    <td>:</td>
                    <td>
                      <?php
                      if ($prospect['hp1']) :
                        echo @hide_5digit_phone($prospect['hp1']);
                      ?>
                        &nbsp;
                        <?php if ($set_time_call == '1') : ?>
                          <a href="#" title="Hand Phone" class="dial-mobile" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['hp1']) ?>','<?= clean_phone($prospect['hp1']) ?>', '<?php echo $totalCallToday; ?>', '9'); return false;">Dial</a>
                          (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['hp1'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['hp1'])); ?>)
                        <?php endif ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['is_priority'] == '0') : ?>
                  <tr style="display:<?php echo strlen($prospect['hp2']) > 4 ? 'table-row' : 'none'; ?>">
                    <td>Dial HP 2</td>
                    <td>:</td>
                    <td>
                      <?php
                      if ($prospect['hp2']) :
                        echo @hide_5digit_phone($prospect['hp2']);
                      ?>
                        &nbsp;
                        <?php if ($set_time_call == '1') : ?>
                          <a href="#" title="Hand Phone 2" class="dial-mobile" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['hp2']) ?>','<?= clean_phone($prospect['hp2']) ?>', '<?php echo $totalCallToday; ?>', '10'); return false;">Dial</a>
                          (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['hp2'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['hp1'])); ?>)
                        <?php endif ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>

                <?php if ($prospect['campaign_product'] == '44' || ($prospect['campaign_product'] == '46' || in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) || $prospect['campaign_product'] == '60' || $prospect['campaign_product'] == '40' || $prospect['campaign_product'] == '32' || $prospect['campaign_product'] == '48' || $prospect['campaign_product'] == '57' || $prospect['campaign_product'] == '59') : //FlexiPay & Cash On Phone & Personal Loan 
                ?>

                  <!-- <tr style="display:<?php echo ($prospect['campaign_product'] == '44' || ($prospect['campaign_product'] == '46' || in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) || $prospect['campaign_product'] == '40' || $prospect['campaign_product'] == '32') ? 'hide' : ''; ?>"> -->
                  <?php
                  $data_offer = json_decode($offers[0]['xsell_cardxsell']);
                  if ($prospect['multiproduct'] == '1') {
                    foreach ($data_offer as $xsell) {
                  ?>
                      <?php if ($xsell == 'FP') { ?>
                        <tr>
                          <td>Simulasi Flexipay</td>
                          <td>:</td>
                          <td>
                            <a href="javascript:installment_simulator('show','<?= $xsell ?>');">Installment Simulator</a>
                          </td>
                        </tr>
                      <?php } elseif ($xsell == 'COP') { ?>
                        <tr>
                          <td>Simulasi COP</td>
                          <td>:</td>
                          <td>
                            <a href="javascript:installment_simulator('show','<?= $xsell ?>');">Installment Simulator</a>
                          </td>
                        </tr>
                      <?php } elseif ($xsell == 'CPIL') { ?>
                        <tr>
                          <td>Simulasi CPIL</td>
                          <td>:</td>
                          <td>
                            <a href="javascript:installment_simulator('show');">Installment Simulator</a>
                          </td>
                        </tr>
                      <?php } elseif ($xsell == 'PL') { ?>
                        <tr>
                          <td>Simulasi Personal Loan</td>
                          <td>:</td>
                          <td>
                            <a href="javascript:installment_simulator('show','<?= $xsell ?>');">Installment Simulator</a>
                          </td>
                        </tr>
                      <?php } ?>

                    <?php }
                  } else { ?>
                    <tr style="display:<?php echo ($prospect['campaign_product'] == '44' || ($prospect['campaign_product'] == '46' || in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) || $prospect['campaign_product'] == '60' || $prospect['campaign_product'] == '40' || $prospect['campaign_product'] == '32' || $prospect['campaign_product'] == '57' || $prospect['campaign_product'] == '59') ? 'hide' : ''; ?>">
                      <td>Simulasi</td>
                      <td>:</td>
                      <td>
                        <a href="javascript:installment_simulator('show');">Installment Simulator</a>
                      </td>
                    </tr>
                  <?php } ?>
                <?php endif; ?>
                <tr class="hide">
                  <td>Dial Additional Phone</td>
                  <td>:</td>
                  <td>
                    <?php
                    if ($prospect['additional_phone']) :
                      echo $prospect['additional_phone'];
                    ?>
                      &nbsp;
                      <a href="#" title="Additional Phone" class="dial-mobile" onclick="sipCallfirst('<?= site_url() ?>/tsr/sip_call/<?= clean_phone($prospect['additional_phone']) ?>','<?= clean_phone($prospect['additional_phone']) ?>', '<?php echo $totalCallToday; ?>', '38'); return false;">Dial</a>
                      (<?php echo $calltrackModel->count_dialledTodayByNumber($prospect['id_prospect'], clean_phone($prospect['additional_phone'])); ?> / <?php echo $calltrackModel->count_dialledBeforeTodayByNumber($prospect['id_prospect'], clean_phone($prospect['additional_phone'])); ?>)
                    <?php endif; ?>
                    <a href="javascript:reqAddPhone();">Request Add New Phone</a> <br />

                  </td>
                </tr>

                <tr style="display:<?php echo $prospect['is_priority'] == '2' ? 'table-row' : 'none'; ?>">
                  <td>Inbound Notes</td>
                  <td>:</td>
                  <td>
                    <span><?= $prospect['inbound_notes']; ?></span>
                  </td>
                </tr>

                <tr id="redialBox" style="display:none">
                  <td>Redial</td>
                  <td>:</td>
                  <td><a id="redialTrigger" onclick="goRedial()" style="cursor:pointer"> Redial </a> </td>
                </tr>


              </table>
            </dt>
          </dl>
          <input type="hidden" id="id_calltrack" name="id_calltrack" value="<?= @$id_calltrack ?>" />
          <input type="hidden" id="id_campaign_hid" name="id_campaign_hid" value="<?= $prospect['id_campaign'] ?>" />
          <input type="hidden" id="id_product" name="id_product" value="<?= $prospect['campaign_product'] ?>" />
          <input type="hidden" id="id_parent" name="id_parent" value="<?= @$id_parent ?>" />
          <input type="hidden" id="id_user" name="id_user" value="<?= $_SESSION["id_user"] ?>" />
          <input type="hidden" id="id_prospect" name="id_prospect" value="<?= $id_prospect ?>" />
          <input type="hidden" id="no_contacted" name="no_contacted" value="" />
          <input type="hidden" id="username" name="username" value="<?= user_name() ?>" />
          <input type="hidden" id="call_attempt" name="call_attempt" value="1" />
          <input type="hidden" id="lastcall_url" name="lastcall_url" value="" />
          <input type="hidden" id="id_spv" name="id_spv" value="<?= $_SESSION["id_leader"]; ?>" />
          <input type="hidden" id="id_tsm" name="id_tsm" value="<?= $_SESSION["id_tsm"]; ?>" />
          <input type="hidden" id="last_weight" name="last_weight" value="<?= $last_call_weight; ?>" />
          <input type="hidden" id="last_agree" name="last_agree" value="<?= $prospect['is_agree']; ?>" />
          <input type="hidden" id="is_validated" name="is_validated" value="0" disabled />
          <input type="hidden" id="xsell" name="xsell" value="" />
          <input type="hidden" id="inbound_uniqueid" name="inbound_uniqueid" value="<?= @$prospect['inbound_uniqueid'] ?>" />

          <h1></h1>
          <?php echo @$product ?>
        </div>

        <div id="remis">
          <input type="button" name="btContacted" value="Connected" onclick="show_contacted()" class="btn-contacted" />
          &nbsp;&nbsp;
          <?php if ($prospect['is_priority'] == 2) { ?>
            <input type="button" name="btUnContacted" value="Disconnected" id="btncall-parent-5" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '186', 'id_callcode', 'weight'); ?>" onclick="go_disconnected()" class="btn-uncontacted" />
          <?php } else { ?>
            <input type="button" name="btUnContacted" value="UnConnected" id="btncall-parent" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '2', 'id_callcode', 'weight'); ?>" onclick="go_uncontacted()" class="btn-uncontacted" />
            &nbsp;&nbsp;
            <input type="button" name="btUnContacted" value="Not Eligible" id="btncall-parent-4" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '4', 'id_callcode', 'weight'); ?>" onclick="go_noteligible()" class="btn-uncontacted" />
          <?php } ?>

        </div>

        <div id="subremis" style="display:none">
          <fieldset>
            <legend>Contacted</legend>
            <div id="subunpresent" style="display:none">
              <?php if ($prospect['is_priority'] == 2) { ?>
                <p><input type="button" name="btContacted" value="PRESENT" onclick="go_present()" class="btn-contacted" /></p>
              <?php } else { ?>
                <p><input type="button" name="btContacted" value="UNPRESENT" id="btncall-parent" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '104', 'id_callcode', 'weight'); ?>" onclick="go_contacted(104)" class="btn-uncontacted" />
                  <input type="button" name="btContacted" value="PRESENT" onclick="go_present()" class="btn-contacted" />
                </p>
              <?php } ?>
            </div>
            <div id="subpresent" style="display:none">
              <p><input type="button" name="btContacted" value="Not Continued" id="btncall-parent-49" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '49', 'id_callcode', 'weight'); ?>" onclick="go_contacted(49)" class="btn-uncontacted" />
                <input type="button" name="btContacted" value="Not Interested" id="btncall-parent-50" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '50', 'id_callcode', 'weight'); ?>" onclick="go_contacted(50)" class="btn-uncontacted" />
                <input type="button" name="btContacted" value="Thinking" id="btncall-parent-51" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '51', 'id_callcode', 'weight'); ?>" onclick="go_contacted(51)" class="btn-contacted" />
              </p>
              <?php if (($prospect['campaign_product'] == '39' || $prospect['campaign_product'] == '59' || $prospect['campaign_product'] == '60' || $prospect['campaign_product'] == '44' || ($prospect['campaign_product'] == '46' || in_array("COP", json_decode($offers[0]['xsell_cardxsell']))) || $prospect['campaign_product'] == '49' || $prospect['campaign_product'] == '32' || $prospect['campaign_product'] == '40' || $prospect['campaign_product'] == '41' || $prospect['campaign_product'] == '42' || $prospect['campaign_product'] == '54' || $prospect['campaign_product'] == '57' || $prospect['campaign_product'] == '58') && $prospect['multiproduct'] != 1) : //Supplement 
              ?>
                <p>
                  <input type="button" id="btn-agree" name="btContacted" value="Agree Main" data-btncamptype="<?= $miscModel->get_tableDataById('tb_product', $prospect['campaign_product'], 'id_product', 'code'); ?>" onclick="go_agree(52)" class="btn-contacted hide" />
                </p>
              <?php endif; ?>
            </div>
          </fieldset>
          <fieldset>
            <legend>UnContacted</legend>
            <p>
              <input type="button" name="btContacted" value="UnContacted" id="btncall-parent" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '3', 'id_callcode', 'weight'); ?>" onclick="show_uncontacted()" class="btn-uncontacted" />
              <input type="button" id="callcode_back2" class="btn-uncontacted" value="Back" name="btnback2" />
            </p>
          </fieldset>
        </div>
        <?php if (($prospect['campaign_product'] == '47' || $prospect['campaign_product'] == '48' || $prospect['campaign_product'] == '50' || $prospect['campaign_product'] == '51' || $prospect['campaign_product'] == '52' || $prospect['campaign_product'] == '53' || $prospect['campaign_product'] == '36') && $prospect['multiproduct'] != '1') : //Supplement 
        ?>
          <div id="multi_offer" style="display: none;">
            <fieldset>
              <legend>Multi Xsell Product Agree</legend>

              <?php foreach ($offers as $offer) : ?>
                <strong>
                  <p>Card : <?= $offer['xsell_cardnumber'] ?></p>
                </strong>
                <strong>
                  <p>Card Type : <?= $offer['xsell_cardtype'] ?></p>
                </strong>
                <strong>
                  <p>Card Owner : <?= $offer['xsell_cardowner'] ?></p>
                </strong>
                <?php
                if ($offer['xsell_cardxsell'] == '') {
                  $multioffer = array(); //no multiproduct
                  //echo '-No Data-';    
                } else {
                  $multioffer = json_decode($offer['xsell_cardxsell']);
                  implode(', ', $multioffer);
                }
                ?>

                <?php
                $multioffcard = $offer['xsell_cardnumber'];
                ?>

                <?php
                $multiidx = $offer['idx'];
                ?>
                <?php
                $agree_ACS1 = $miscModel->get_tableDataById_buytype('tb_prospect_print', $prospect['id_prospect'], 'apl_prospectid', 'ACS', 'buy_type', 'buy_type');
                ?>

                <?php if (COUNT($multioffer) > 0) : ?>
                  <!-- BUG: <input type="button" id="btn-agree" name="btContacted" value="Agree Main" data-btncamptype="<?= $miscModel->get_tableDataById('tb_product', $prospect['campaign_product'], 'id_product', 'code'); ?>" onclick="go_agree(52)" class="btn-contacted "/>  -->
                  <?php foreach ($multioffer as $multioffers) : ?>
                    <?php if (!empty($multioffers)) { ?>
                      <input type="button" id="btn-agree-<?= $multioffers; ?>-<?= $multiidx; ?>" name="btContacted" data-btncamptype="<?= $multioffers; ?>" value="<?= $miscModel->get_tableDataById('tb_product', $multioffers, 'code', 'name'); ?>" onclick="go_agree(52, '<?= $multioffers; ?>','<?= $multioffcard; ?>','<?= $multiidx; ?>');parsexsell(this.id);" class="btn-contacted" />
                    <?php } ?>
                  <?php endforeach; ?>
                  <?php if ($agree_ACS1 == 'ACS') { ?>
                    &nbsp;&nbsp;
                    <input type="button" name="btUnContacted" value="Waiting KTP" id="btncall-parent" data-weight="<?= $miscModel->get_tableDataById('tb_callcode', '184', 'id_callcode', 'weight'); ?>" onclick="go_waitingktp()" class="btn-uncontacted" />
                  <?php } ?>
                <?php endif; ?>
                <?php if ($prospect['is_priority'] == '1') { ?>
                  <input type="button" id="btn-next" name="btContacted" value="Next Customer" onclick="jump_customer('<?= site_url() ?>tsr/main/<?= $prospect['id_campaign']; ?>')" class="btn-uncontacted " />
                  <hr />
                <?php } else { ?>
                  <input type="button" id="btn-next" name="btContacted" value="Next Customer" onclick="jump_customer('<?= site_url() ?>tsr/main/<?= $prospect['id_campaign']; ?>')" class="btn-uncontacted hide" />
                  <hr />
                <?php } ?>
              <?php endforeach; ?>

            </fieldset>
          </div>
        <?php endif; ?>

        <?php if ($prospect['multiproduct'] == '1') : // XSELL ALL 
        ?>
          <div id="multi_offer" style="display: none;">
            <fieldset>
              <legend>Multi Xsell Product Agree </legend>

              <?php foreach ($offers as $offer) : ?>
                <strong>
                  <p>Card : <?= $offer['xsell_cardnumber'] ?></p>
                </strong>
                <strong>
                  <p>Card Type : <?= $offer['xsell_cardtype'] ?></p>
                </strong>
                <strong>
                  <p>Card Owner : <?= $offer['xsell_cardowner'] ?></p>
                </strong>
                <?php
                if ($offer['xsell_cardxsell'] == '') {
                  $multioffer = array(); //no multiproduct
                  //echo '-No Data-';    
                } else {
                  $multioffer = json_decode($offer['xsell_cardxsell']);
                  implode(', ', $multioffer);
                }
                ?>

                <?php
                $multioffcard = $offer['xsell_cardnumber'];
                ?>

                <?php
                $multiidx = $offer['idx'];
                ?>
                <?php
                $agree_ACS = $miscModel->get_tableDataById('tb_prospect_print', $prospect['id_prospect'], 'apl_prospectid', 'buy_type');
                ?>

                <?php if (COUNT($multioffer) > 0) : ?>
                  <?php foreach ($multioffer as $multioffers) : ?>
                    <?php if (!empty($multioffers)) { ?>
                      <?php if ($prospect['campaign_product'] == '48') { ?>
                        <?php if ($agree_ACS != 'ACS' && $multioffers != 'ACS') { ?>
                          <input style="padding: 0 5px 0 5px; width: auto;" type="button" id="btn-agree-<?= $multioffers; ?>-<?= $multiidx; ?>" name="btContacted" data-btncamptype="<?= $multioffers; ?>" value="<?= $miscModel->get_tableDataById('tb_product', $multioffers, 'code', 'name'); ?>" onclick="go_agree(52, '<?= $multioffers; ?>','<?= $multioffcard; ?>','<?= $multiidx; ?>');parsexsell(this.id);" class="btn-contacted btn-agree" readonly disabled />
                        <?php } else { ?>
                          <input style="padding: 0 5px 0 5px; width: auto;" type="button" id="btn-agree-<?= $multioffers; ?>-<?= $multiidx; ?>" name="btContacted" data-btncamptype="<?= $multioffers; ?>" value="<?= $miscModel->get_tableDataById('tb_product', $multioffers, 'code', 'name'); ?>" onclick="go_agree(52, '<?= $multioffers; ?>','<?= $multioffcard; ?>','<?= $multiidx; ?>');parsexsell(this.id);" class="btn-contacted" />
                        <?php }  ?>
                      <?php } else { ?>
                        <input style="padding: 0 5px 0 5px; width: auto;" type="button" id="btn-agree-<?= $multioffers; ?>-<?= $multiidx; ?>" name="btContacted" data-btncamptype="<?= $multioffers; ?>" value="<?= $miscModel->get_tableDataById('tb_product', $multioffers, 'code', 'name'); ?>" onclick="go_agree(52, '<?= $multioffers; ?>','<?= $multioffcard; ?>','<?= $multiidx; ?>');parsexsell(this.id);" class="btn-contacted" />
                      <?php } ?>

                    <?php } ?>
                  <?php endforeach; ?>

                <?php endif; ?>
                <?php if ($prospect['is_priority'] == '1') { ?>
                  <input type="button" id="btn-next" name="btContacted" value="Next Customer" onclick="jump_customer('<?= site_url() ?>tsr/main/<?= $prospect['id_campaign']; ?>')" class="btn-uncontacted " />
                  <hr />
                <?php } else { ?>
                  <input type="button" id="btn-next" name="btContacted" value="Next Customer" onclick="jump_customer('<?= site_url() ?>tsr/main/<?= $prospect['id_campaign']; ?>')" class="btn-uncontacted hide" />
                  <hr />
                <?php } ?>
              <?php endforeach; ?>

            </fieldset>
          </div>
        <?php endif; ?>
        <!-- <div id="multi_subremis" style="display:none">
              <fieldset>
                  <legend>Multi Product Agree</legend>
                        <?php if (COUNT($multiproducts) > 0) : ?>
                        <input type="button" id="btn-agree" name="btContacted" value="Agree Main" data-btncamptype="<?= $miscModel->get_tableDataById('tb_product', $prospect['campaign_product'], 'id_product', 'code'); ?>" onclick="go_agree(52)" class="btn-contacted hide"/>
                            <?php foreach ($multiproducts as $multiproduct) : ?>
                                <input type="button" id="btn-agree-<?= $multiproduct; ?>" name="btContacted" data-btncamptype="<?= $multiproduct; ?>" value="Agree <?= $multiproduct; ?>" onclick="go_agree(52, '<?= $multiproduct; ?>')" class="btn-contacted hide" />
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <input type="button" id="btn-next" name="btContacted" value="Next Customer" onclick="jump_customer('<?= site_url() ?>tsr/main/<?= $prospect['id_campaign']; ?>')" class="btn-uncontacted hide"/>
              </fieldset>
              </div>-->

        <div class="det-right" style="display:none">
          <div class="connect" id="connect">
          </div>
        </div>


      <?php endif; ?>
    <?php endif; ?>

  </div> <!-- End Class Info -->
  <?php
  //    var_dump($_SESSION);
  ?>


  <div class="last-agree">
    <h1>Last 10 Calltrack</h1>
    <div class="box">
      <?php echo $last_callhistory; ?>
    </div>
  </div>

</div>
<!--<table class="userlist" style="width:730px">
  <tr>
    <th>Campaign</th>
    <th>New Data</th>
    <th>Close Lost</th>
    <th>Disagree</th>
    <th>Follow Up</th>
    <th>Not Contact</th>
    <th>Unconnect</th>
    <th>Total Data</th>
    
  </tr>
  
</table>-->
<!-- Last Remark Old 
       <p class="space"></p>
         <div class="last-remark">
            <h1>Last Remark</h1>
             <?php if (!empty($last_remark)) : ?>
              <i><?php echo $last_remark[0]['last_calltime'] ?></i>, <?php echo $last_remark[0]['last_remark'] ?> (<?php echo $last_remark[0]['remark_callcode'] ?>)
             <?php endif; ?>
         </div>
-->
<!-- Last Remark -->

<!--
        <p class="space"></p>
         <div class="last-remark">
            <h1>Last Remark</h1>
             <?php if (!empty($last_remark)) : ?>
              <?php foreach ($last_remark as $remark) { ?>
                <p><?php echo $remark['call_date'] . ' ' . $remark['call_time']; ?>, <?php echo $remark['username'] ?> - <?php echo $remark['remark'] ?> ( <?php echo $remark['name_callcode']; ?>)</p>   
             <?php }
              endif; ?>
         </div>
-->

<p class="space"></p>
<div class="last-remark">

</div>

<div class="rem-list">
  <h1>Reminder List</h1>
  <div style="width:1000px;height:130px;overflow-y:scroll">
    <?php echo $reminder ?>
  </div>
</div>

</div> <!-- End Class Tele -->

<!-- Zip code Finder -->
<div id="zipcode_finder">
  <fieldset>
    <legend>Search</legend>
    <p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>
    <div> <input type="text" id="searchkey" name="searchkey" placeholder="keyword" list="autocomplete" size="70" /> </div>
    <datalist id="autocomplete"></datalist>
    <br />
    <div>
      <input type="radio" name="type" value="kota" checked="true" onclick="updAutoComplete()" /> Kota &nbsp
      <input type="radio" name="type" value="kecamatan" onclick="updAutoComplete()" /> Kecamatan &nbsp
      <input type="radio" name="type" value="kelurahan" onclick="updAutoComplete()" /> Kelurahan &nbsp
      <input type="radio" name="type" value="zipcode" onclick="updAutoComplete()" /> Zipcode &nbsp
    </div>
    <br />
  </fieldset>

  <table style="width:98%">
    <thead>
      <tr>
        <th class="td1">#</th>
        <th class="td2">Kota</th>
        <th class="td3">Kecamatan</th>
        <th class="td4">Kelurahan</th>
        <th class="td5">Zipcode</th>
        <th class="td6">Zone</th>
      </tr>
    </thead>
  </table>

  <div style="width:100%; max-height:400px; height:90%; overflow:scroll">
    <table style="width:100%">
      <tbody id="areaTable" style="width:100%">

      </tbody>
    </table>
  </div>

</div>

<!-- Zip code Finder -->
<div id="zipcode_finder">
  <fieldset>
    <legend>Search</legend>
    <p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>
    <div> <input type="text" id="searchkey" name="searchkey" placeholder="keyword" list="autocomplete" size="70" /> </div>
    <datalist id="autocomplete"></datalist>
    <br />
    <div>
      <input type="radio" name="type" value="kota" checked="true" onclick="updAutoComplete()" /> Kota &nbsp
      <input type="radio" name="type" value="kecamatan" onclick="updAutoComplete()" /> Kecamatan &nbsp
      <input type="radio" name="type" value="kelurahan" onclick="updAutoComplete()" /> Kelurahan &nbsp
      <input type="radio" name="type" value="zipcode" onclick="updAutoComplete()" /> Zipcode &nbsp
    </div>
    <br />
  </fieldset>

  <table style="width:98%">
    <thead>
      <tr>
        <th class="td1">#</th>
        <th class="td2">Kota</th>
        <th class="td3">Kecamatan</th>
        <th class="td4">Kelurahan</th>
        <th class="td5">Zipcode</th>
        <th class="td6">Zone</th>
      </tr>
    </thead>
  </table>

  <div style="width:100%; max-height:400px; height:90%; overflow:scroll">
    <table style="width:100%">
      <tbody id="areaTable" style="width:100%">

      </tbody>
    </table>
  </div>

</div>

<!-- Simulator Cicilan -->
<?php if (@$prospect['multiproduct'] != '1') { ?>
  <?php if (@$prospect['campaign_product'] != '32' && @$prospect['campaign_product'] != '44' && @$prospect['campaign_product'] != '60' && @$prospect['campaign_product'] != '59' && @$prospect['campaign_product'] != 57) : ?>
    <div id="installment_simulator" style="height: auto;">
      <fieldset>
        <legend>Installment Simulator</legend>
        <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
        <table style="overflow: auto; width: 100%">
          <tr>
            <td style="width:175px">Nominal</td>
            <td>:</td>
            <td style="width:10px">Rp.</td>
            <td>
              <div> <input type="text" id="simulator_sum" name="simulator_sum" value="0" size="70" style="width:300px" /> </div>
            </td>
          </tr>
          <tr>
            <td>Tenor</td>
            <td>:</td>
            <td></td>
            <td>
              <div> <input type="text" id="simulator_tenure" name="simulator_tenure" value="0" size="70" style="width:35px" /> Bulan </div>
            </td>
          </tr>
          <tr>
            <td>Bunga Bulanan</td>
            <td>:</td>
            <td></td>
            <td>
              <div> <input type="text" id="simulator_interest_monthly" name="simulator_interest_monthly" value="0" size="70" style="width:35px" /> % | *Gunakan Titik (.) untuk Desimal</div>
            </td>
          </tr>
          <tr class="hide">
            <td>Bunga Tahunan</td>
            <td>:</td>
            <td></td>
            <td>
              <div> <input type="text" id="simulator_interest" name="simulator_interest" value="0" size="70" style="width:35px" /> % | *Gunakan Titik (.) untuk Desimal</div>
            </td>
          </tr>
          <tr>
            <td>Cicilan dengan Bunga Bulanan</td>
            <td>:</td>
            <td>Rp.</td>
            <td>
              <div> <input type="text" id="simulator_installment_monthly" name="simulator_installment_monthly" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
            </td>
          </tr>
          <tr class="hide">
            <td>Cicilan dengan Bunga Tahunan</td>
            <td>:</td>
            <td>Rp.</td>
            <td>
              <div> <input type="text" id="simulator_installment" name="simulator_installment" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
            </td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <input type="button" class="btn-contacted" id="btn-hitung_simulator" value="Hitung" onclick="simulateInstallment()" /> &nbsp;
              <input type="button" class="btn-contacted" id="btn-reset_simulator" value="Reset" onclick="resetInstallment()" /> &nbsp;
              <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide')" />
            </td>
          </tr>
        </table>
        <br />
      </fieldset>
      <fieldset>
        <legend>Installment Tree</legend>
        <table style="overflow: auto; width: 100%">
          <?php foreach ($list_productcode as $prodcode) : ?>
            <?php
            if (empty($refreshbag)) {
              $pinjaman = $prospect['max_loan'] * 1;
            } else {
              $pinjaman = $refreshbag['max_loan'] * 1;
            }

            $tenor = $prodcode['tenor'] * 1;
            $cicilan_pokok = $pinjaman / $tenor;
            $cicilan_bunga = $pinjaman * ($prodcode['bunga'] / 100);
            $jml_angsuran = round($cicilan_pokok + $cicilan_bunga);
            ?>
            <tr>
              <td style="width:175px"><?= $prodcode['status'] ?> - <?= $prodcode['tenor'] ?> Bulan - <?= $prodcode['bunga'] ?>%</td>
              <td>:</td>
              <td style="width:10px">Rp. </td>
              <td><?= price_format($jml_angsuran); ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </fieldset>
    </div>
  <?php elseif ($prospect['campaign_product'] == '44') : ?>
    <div id="installment_simulator">
      <fieldset>
        <legend>Installment Simulator FOP</legend>
        <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
        <table style="overflow: auto; width: 100%">
          <tr>
            <td>Nominal</td>
            <td>:</td>
            <td style="width:5px">Rp.</td>
            <td>
              <div style="font-size: 14px;">
                <input type="number" min="500000" step="500000" id="simulator_plafon" name="simulator_plafon" value="500000" style="width:150px; font-size: 14px;" />
              </div>
            </td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td colspan="2" style="">
              <input type="button" style="width: 100px; margin-left: 28px;" class="btn-contacted" id="" value="Hitung Cicilan" onclick="simulasiPinjaman1()">
              <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide')" />
            </td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend>Installment Tree</legend>
        <table style="overflow: auto; width: 100%">
          <tr>
            <td></td>
            <td colspan="2">
              <div id="result_simulator" style="font-size: 13px;">
                <?php $z = 0;
                foreach ($list_productcode as $prodcode) { ?>
                  <label> Flexipay / <?= $prodcode['bunga'] ?>% / <?= $prodcode['tenor'] ?> Bulan / Bunga Efektif <?= $prodcode['bunga_efektif'] * 100 ?>% / Cicilan: <span id="angsuranCal_<?= $z; ?>"></span> / EBR: <span id="angsuranEbr_<?= $z; ?>"></span> </label></br><br>
                  <input type="hidden" id="bunga_<?= $z; ?>" value="<?= $prodcode['bunga']; ?>">
                  <input type="hidden" id="tenor_<?= $z; ?>" value="<?= $prodcode['tenor']; ?>">
                <?php $z++;
                } ?>
              </div>
            </td>
          </tr>
        </table>
        <br />
      </fieldset>
    </div>
    <!-- Simulasi Personal Loan -->
    <script type="text/javascript">
      function fixInputCal() {
        var input_pinjaman = $('#simulator_plafon').val();
        var max_pinjaman = 100000000;
        var fix_pinjaman;
        fix_pinjaman = input_pinjaman.replace(/[^0-9]/g, '');
        fix_pinjaman = parseInt(fix_pinjaman);

        var min_pinjaman = 500000;

        if (isNaN(fix_pinjaman)) {
          new Boxy.alert('Mohon Nominal tidak diisi dengan huruf, simbol atau tanda baca apapun, termasuk koma (,) dan Titik (.)');
          $('#ben_pinjamincome').val('0');
        } else if (input_pinjaman < min_pinjaman) {
          new Boxy.alert('Nominal terlalu kecil. Minimal : 500.000');
          $('#simulator_plafon').val(min_pinjaman);
        } else {
          $('#simulator_plafon').val(fix_pinjaman);
        }

      }

      function simulasiPinjaman1() {
        fixInputCal();
        var pinjaman = $('#simulator_plafon').val() * 1;

        for (var i = 0; i < 4; i++) {
          var bunga = $('#bunga_' + i).val() * 1;
          var tenor = $('#tenor_' + i).val() * 1;
          var cicilan_pokok = pinjaman / tenor;
          var cicilan_bunga = pinjaman * (bunga / 100);
          var jml_angsuran = Math.round(cicilan_pokok + cicilan_bunga);

          // Script New
          var cicilan_asli = pinjaman * (tenor + 1);
          var cicilan_bung = cicilan_asli / 2;
          var hasil = Math.round(cicilan_bung * (bunga / 100));

          $('#angsuranCal_' + i).empty();
          $('#angsuranCal_' + i).append(jml_angsuran);

          $('#angsuranCal_' + i).priceFormat({
            prefix: 'Rp.',
            thousandsSeparator: ',',
            centsLimit: 0
          });

          $('#angsuranEbr_' + i).empty();
          $('#angsuranEbr_' + i).append(hasil);
          $('#angsuranEbr_' + i).priceFormat({
            prefix: 'Rp.',
            thousandsSeparator: ',',
            centsLimit: 0
          });
        }
      }

      $(document).ready(function() {
        simulasiPinjaman1();
      });
    </script>
  <?php elseif ($prospect['campaign_product'] == '60') : ?>
    <div id="installment_simulator">
      <fieldset>
        <legend>Installment Simulator FOP Statemnt</legend>
        <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
        <table style="overflow: auto; width: 100%">
          <tr>
            <td>Nominal</td>
            <td>:</td>
            <td style="width:5px">Rp.</td>
            <td>
              <div style="font-size: 14px;">
                <input type="number" min="500000" step="500000" id="simulator_plafon" name="simulator_plafon" value="500000" style="width:150px; font-size: 14px;" />
              </div>
            </td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td colspan="2" style="">
              <input type="button" style="width: 100px; margin-left: 28px;" class="btn-contacted" id="" value="Hitung Cicilan" onclick="simulasiPinjaman1()">
              <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide')" />
            </td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend>Installment Tree</legend>
        <table style="overflow: auto; width: 100%">
          <tr>
            <td></td>
            <td colspan="2">
              <div id="result_simulator" style="font-size: 13px;">
                <?php $z = 0;
                foreach ($list_productcode as $prodcode) { ?>
                  <label> Flexipay / <?= $prodcode['bunga'] ?>% / <?= $prodcode['tenor'] ?> Bulan / Bunga Efektif <?= $prodcode['bunga_efektif'] * 100 ?>% / Cicilan: <span id="angsuranCal_<?= $z; ?>"></span> / EBR: <span id="angsuranEbr_<?= $z; ?>"></span> </label></br><br>
                  <input type="hidden" id="bunga_<?= $z; ?>" value="<?= $prodcode['bunga']; ?>">
                  <input type="hidden" id="tenor_<?= $z; ?>" value="<?= $prodcode['tenor']; ?>">
                <?php $z++;
                } ?>
              </div>
            </td>
          </tr>
        </table>
        <br />
      </fieldset>
    </div>
    <!-- Simulasi Personal Loan -->
    <script type="text/javascript">
      function fixInputCal() {
        var input_pinjaman = $('#simulator_plafon').val();
        var max_pinjaman = 100000000;
        var fix_pinjaman;
        fix_pinjaman = input_pinjaman.replace(/[^0-9]/g, '');
        fix_pinjaman = parseInt(fix_pinjaman);

        var min_pinjaman = 500000;

        if (isNaN(fix_pinjaman)) {
          new Boxy.alert('Mohon Nominal tidak diisi dengan huruf, simbol atau tanda baca apapun, termasuk koma (,) dan Titik (.)');
          $('#ben_pinjamincome').val('0');
        } else if (input_pinjaman < min_pinjaman) {
          new Boxy.alert('Nominal terlalu kecil. Minimal : 500.000');
          $('#simulator_plafon').val(min_pinjaman);
        } else {
          $('#simulator_plafon').val(fix_pinjaman);
        }

      }

      function simulasiPinjaman1() {
        fixInputCal();
        var pinjaman = $('#simulator_plafon').val() * 1;

        for (var i = 0; i < 5; i++) {
          var bunga = $('#bunga_' + i).val() * 1;
          var tenor = $('#tenor_' + i).val() * 1;
          var cicilan_pokok = pinjaman / tenor;
          var cicilan_bunga = pinjaman * (bunga / 100);
          var jml_angsuran = Math.round(cicilan_pokok + cicilan_bunga);

          // Script New
          var cicilan_asli = pinjaman * (tenor + 1);
          var cicilan_bung = cicilan_asli / 2;
          var hasil = Math.round(cicilan_bung * (bunga / 100));

          $('#angsuranCal_' + i).empty();
          $('#angsuranCal_' + i).append(jml_angsuran);

          $('#angsuranCal_' + i).priceFormat({
            prefix: 'Rp.',
            thousandsSeparator: ',',
            centsLimit: 0
          });

          $('#angsuranEbr_' + i).empty();
          $('#angsuranEbr_' + i).append(hasil);
          $('#angsuranEbr_' + i).priceFormat({
            prefix: 'Rp.',
            thousandsSeparator: ',',
            centsLimit: 0
          });
        }
      }

      $(document).ready(function() {
        simulasiPinjaman1();
      });
    </script>
  <?php elseif ($prospect['campaign_product'] == '32') : ?>
    <div id="installment_simulator">
      <fieldset>
        <legend>Installment Simulator</legend>
        <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
        <table>
          <tr>
            <td>Nominal</td>
            <td>:</td>
            <td style="width:5px">Rp.</td>
            <td>
              <div style="font-size: 14px;">
                <input type="number" min="5000000" step="500000" id="simulator_plafon" name="simulator_plafon" value="<?= $prospect['plafon12']; ?>" style="width:150px; font-size: 14px;" /> / Maksimal Pinjaman Rp<?php echo number_format($prospect['plafon12']) ?>
              </div>
            </td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <input type="button" style="width: 100px;" class="btn-contacted" id="" value="Hitung Cicilan" onclick="simulasiPinjaman1()">
              <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide')" />
            </td>
          </tr>
        </table>
      </fieldset>
      <fieldset>
        <legend>Installment Tree</legend>
        <table>
          <tr>
            <td></td>
            <td colspan="2">
              <div id="result_simulator" style="font-size: 13px;">
                <?php $z = 0;
                foreach ($list_productcode as $prodcode) { ?>
                  <label> Personal Loan / <?= $prodcode['bunga'] ?>% / <?= $prodcode['tenor'] ?> Bulan / Bunga Efektif <?= $prodcode['bunga_efektif'] ?>% / Cicilan: <span id="angsuranCal_<?= $z; ?>"></span></label></br><br>
                  <input type="hidden" id="bunga_<?= $z; ?>" value="<?= $prodcode['bunga']; ?>">
                  <input type="hidden" id="tenor_<?= $z; ?>" value="<?= $prodcode['tenor']; ?>">
                <?php $z++;
                } ?>
              </div>
            </td>
          </tr>
        </table>
        <br />
      </fieldset>
    </div>
    <!-- Simulasi Personal Loan -->
    <script type="text/javascript">
      function fixInputCal() {
        var input_pinjaman = $('#simulator_plafon').val();
        var max_pinjaman = <?= $prospect['plafon12'] ?> * 1;
        var fix_pinjaman;
        fix_pinjaman = input_pinjaman.replace(/[^0-9]/g, '');
        fix_pinjaman = parseInt(fix_pinjaman);

        var min_pinjaman = 5000000;

        if (isNaN(fix_pinjaman)) {
          new Boxy.alert('Mohon Pinjaman tidak diisi dengan huruf, simbol atau tanda baca apapun, termasuk koma (,) dan Titik (.)');
          $('#ben_pinjamincome').val('0');
        } else if (input_pinjaman > max_pinjaman) {
          new Boxy.alert('Pinjaman melebihi limit dari tenor yang tersedia, mohon dicek kembali. ');
          $('#simulator_plafon').val(max_pinjaman);
        } else if (input_pinjaman < min_pinjaman) {
          new Boxy.alert('Pinjaman terlalu kecil. Minimal : 5.000.000');
          $('#simulator_plafon').val(min_pinjaman);
        } else {
          $('#simulator_plafon').val(fix_pinjaman);
        }
      }

      function simulasiPinjaman1() {
        fixInputCal();
        var pinjaman = $('#simulator_plafon').val() * 1;

        for (var i = 0; i < 4; i++) {
          var bunga = $('#bunga_' + i).val() * 1;
          var tenor = $('#tenor_' + i).val() * 1;
          var cicilan_pokok = pinjaman / tenor;
          var cicilan_bunga = pinjaman * (bunga / 100);
          var jml_angsuran = Math.round(cicilan_pokok + cicilan_bunga);

          $('#angsuranCal_' + i).empty();
          $('#angsuranCal_' + i).append(jml_angsuran);

          $('#angsuranCal_' + i).priceFormat({
            prefix: 'Rp.',
            thousandsSeparator: ',',
            centsLimit: 0
          });
        }
      }

      $(document).ready(function() {
        simulasiPinjaman1();
      });
    </script>
  <?php elseif ($prospect['campaign_product'] == '57') : ?>
    <div id="installment_simulator" style="height: auto;">
      <fieldset>
        <legend>Installment Simulator CPIL</legend>
        <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
        <table style="overflow: auto; width: 100%">
          <tr>
            <td style="width:175px">Nominal</td>
            <td>:</td>
            <td style="width:10px">Rp.</td>
            <!-- <td>
              <div> -->
            <!-- <input type="text" id="simulator_sum" name="simulator_sum" value="0" size="70" style="width:300px" /> -->
            <!-- <input type="text" id="simulator_sum" name="simulator_sum" value="0" size="70" style="width:300px" /> -->
            <!-- <input type="text" id="simulator_sum" name="simulator_sum" oninput="titikOtomatis(this)" value="0" size="70" style="width:300px" /> -->

            <!-- </div>
            </td> -->
            <td>
              <div> <input type="text" id="simulator_sum3" name="simulator_sum3" onchange="hitungkomas()" value="0" size="70" style="width:300px" /> </div>
            </td>
            <td style="display: none;">
              <div> <input type="text" id="simulator_sum2" name="simulator_sum2" value="0" size="70" style="width:300px" /> </div>
            </td>
          </tr>
          <tr>
            <td>Tenor</td>
            <td>:</td>
            <td></td>
            <td>
              <div>
                <!-- <input type="text" id="simulator_tenure" name="simulator_tenure" value="0" size="70" style="width:35px" /> -->
                <select name="simulator_tenure" id="simulator_tenure">
                  <!-- <?php foreach ($list_productcode as $teno) : ?> -->
                  <!-- <option value="<?= $teno['tenor'] ?>"><?= $teno['tenor'] ?></option> -->
                  <!-- <?php endforeach; ?> -->
                  <option value="3">3</option>
                  <option value="6">6</option>
                  <option value="12">12</option>
                  <option value="24">24</option>
                  <option value="36">36</option>
                  <option value="48">48</option>
                </select>
                Bulan
              </div>
            </td>
          </tr>
          <tr>
            <td>Bunga Bulanan</td>
            <td>:</td>
            <td></td>
            <td>
              <div>
                <!-- <input type="text" id="simulator_interest_monthly" name="simulator_interest_monthly" value="0" size="70" style="width:35px" />  -->
                <select name="simulator_interest_monthly" id="simulator_interest_monthly">
                  <!-- <?php foreach ($list_productcode as $month) : ?> -->
                  <!-- <option value="<?= $month['bunga'] ?>"><?= $month['bunga'] ?></option> -->
                  <!-- <?php endforeach; ?> -->
                  <option value="0.49">0.49</option>
                  <option value="0.59">0.59</option>
                  <option value="0.69">0.69</option>
                  <option value="0.79">0.79</option>
                  <option value="0.89">0.89</option>
                  <option value="0.99">0.99</option>
                  <option value="1.15">1.15</option>
                  <option value="1.25">1.25</option>
                  <option value="1.30">1.30</option>
                  <option value="1.50">1.50</option>
                  <option value="1.59">1.59</option>
                  <option value="1.68">1.68</option>
                </select>
                % | *Gunakan Titik (.) untuk Desimal
              </div>
            </td>
          </tr>
          <tr class="hide">
            <td>Bunga Tahunan</td>
            <td>:</td>
            <td></td>
            <td>
              <div> <input type="text" id="simulator_interest" name="simulator_interest" value="0" size="70" style="width:35px" /> % | *Gunakan Titik (.) untuk Desimal</div>
            </td>
          </tr>
          <tr>
            <td>Cicilan dengan Bunga Bulanan</td>
            <td>:</td>
            <td>Rp.</td>
            <td>
              <div> <input type="text" id="simulator_installment_monthly" name="simulator_installment_monthly" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
            </td>
          </tr>
          <tr>
            <td>EBR</td>
            <td>:</td>
            <td>Rp.</td>
            <td>
              <div> <input type="text" id="simulator_installment_ebr" name="simulator_installment_ebr" placeholder="EBR" size="70" style="width:300px" readonly=true /> </div>
            </td>
          </tr>
          <tr class="hide">
            <td>Cicilan dengan Bunga Tahunan</td>
            <td>:</td>
            <td>Rp.</td>
            <td>
              <div> <input type="text" id="simulator_installment" name="simulator_installment" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
            </td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <input type="button" class="btn-contacted" id="btn-hitung_simulator" value="Hitung" onclick="simulateInstallments()" /> &nbsp;
              <input type="button" class="btn-contacted" id="btn-reset_simulator" value="Reset" onclick="resetInstallments()" /> &nbsp;
              <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide')" />
            </td>
          </tr>
        </table>
        <br />
      </fieldset>
      <fieldset class="hide">
        <legend>Installment Tree</legend>
        <table style="overflow: auto; width: 100%">
          <?php foreach ($list_productcode as $prodcode) : ?>
            <?php
            if (empty($refreshbag)) {
              $pinjaman = $prospect['max_loan'] * 1;
            } else {
              $pinjaman = $refreshbag['max_loan'] * 1;
            }

            $tenor = $prodcode['tenor'] * 1;
            $cicilan_pokok = $pinjaman / $tenor;
            $cicilan_bunga = $pinjaman * ($prodcode['bunga'] / 100);
            $jml_angsuran = round($cicilan_pokok + $cicilan_bunga);
            ?>
            <tr>
              <td style="width:175px"> <?= $prodcode['tenor'] ?> Bulan - <?= $prodcode['bunga'] ?>%</td>
              <!-- <td style="width:175px"><?= $prodcode['status'] ?> - <?= $prodcode['tenor'] ?> Bulan - <?= $prodcode['bunga'] ?>%</td> -->
              <td>:</td>
              <td style="width:10px">Rp. </td>
              <td><?= price_format($jml_angsuran); ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </fieldset>
    </div>
    <script type="text/javascript">
      function simulateInstallments() {
        var loan = $('#simulator_sum2').val() * 1;
        var tenure = $('#simulator_tenure').val() * 1;
        var interest = $('#simulator_interest').val() * 1;
        var interest_mothly = $('#simulator_interest_monthly').val() * 1;
        //var installment  = $('#simulator_installment').val();
        var cicilan_pokok = loan / tenure;
        var cicilan_bunga = loan * (interest_mothly / 100);
        var jml_angsuran = cicilan_pokok + cicilan_bunga;

        interest = loan * ((interest / 12) / 100);
        var principal = (loan / tenure) * 1;
        var installment = (principal + interest) * 1;

        // EBR
        var cicilan_asli = loan * (tenure + 1);
        var cicilan_bung = cicilan_asli / 2;
        var hasil = cicilan_bung * (interest_mothly / 100);

        $('#simulator_installment').val(Math.round(installment));
        $('#simulator_installment').priceFormat({
          prefix: '',
          thousandsSeparator: ',',
          centsLimit: 0
        });

        $('#simulator_installment_monthly').val(Math.round(jml_angsuran));
        $('#simulator_installment_monthly').priceFormat({
          prefix: '',
          thousandsSeparator: ',',
          centsLimit: 0
        });

        $('#simulator_installment_ebr').val(Math.round(hasil));
        $('#simulator_installment_ebr').priceFormat({
          prefix: '',
          thousandsSeparator: ',',
          centsLimit: 0
        });
      }

      function resetInstallments() {
        $('#simulator_sum2').val(0);
        $('#simulator_sum3').val(0);
        $('#simulator_tenure').val(0);
        $('#simulator_installment').val(0);
        $('#simulator_interest').val(0);
        $('#simulator_interest_monthly').val(0);
        $('#simulator_installment_monthly').val(0);
      }

      function hitungkomas() {
        $('#simulator_sum3').priceFormat({
          prefix: '',
          thousandsSeparator: ',',
          centsLimit: 0
        });
        var loan1 = $('#simulator_sum3').val();
        $('#simulator_sum2').val(loan1);
        $('#simulator_sum2').priceFormat({
          prefix: '',
          thousandsSeparator: '',
          centsLimit: 0
        });

        //alert(sum);
        //var loan = $('#simulator_sum').val(loan1);
        //alert(loan);
        //$('#simulator_sum').val(Math.round(loan1)); 

        simulateInstallments();
      }

      function autofillSimulator() {
        <?php if (empty($refreshbag)) : ?>
          var nominal = "<?= @$prospect['available_credit']; ?>";
        <?php else : ?>
          var nominal = "<?= @$refreshbag['available_credit']; ?>";
        <?php endif; ?>
        var tenor = 6;
        var bunga = 0.89;

        $('#simulator_sum2').val(nominal);
        $('#simulator_sum3').val(nominal);
        $('#simulator_tenure').val(tenor);
        $('#simulator_interest_monthly').val(bunga);

        simulateInstallments();
      }
    </script>
  <?php elseif ($prospect['campaign_product'] == '59') : ?>
    <div id="installment_simulator" style="height: auto;">
      <fieldset>
        <legend>Installment Simulator CPILD1</legend>
        <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
        <table style="overflow: auto; width: 100%">
          <tr>
            <td style="width:175px">Nominal</td>
            <td>:</td>
            <td style="width:10px">Rp.</td>
            <!-- <td>
              <div> -->
            <!-- <input type="text" id="simulator_sum" name="simulator_sum" value="0" size="70" style="width:300px" /> -->
            <!-- <input type="text" id="simulator_sum" name="simulator_sum" value="0" size="70" style="width:300px" /> -->
            <!-- <input type="text" id="simulator_sum" name="simulator_sum" oninput="titikOtomatis(this)" value="0" size="70" style="width:300px" /> -->

            <!-- </div>
            </td> -->
            <td>
              <div> <input type="text" id="simulator_sum3" name="simulator_sum3" onchange="hitungkomas()" value="0" size="70" style="width:300px" /> </div>
            </td>
            <td style="display: none;">
              <div> <input type="text" id="simulator_sum2" name="simulator_sum2" value="0" size="70" style="width:300px" /> </div>
            </td>
          </tr>
          <tr>
            <td>Tenor</td>
            <td>:</td>
            <td></td>
            <td>
              <div>
                <!-- <input type="text" id="simulator_tenure" name="simulator_tenure" value="0" size="70" style="width:35px" /> -->
                <select name="simulator_tenure" id="simulator_tenure">
                  <!-- <?php foreach ($list_productcode as $teno) : ?> -->
                  <!-- <option value="<?= $teno['tenor'] ?>"><?= $teno['tenor'] ?></option> -->
                  <!-- <?php endforeach; ?> -->
                  <option value="3">3</option>
                  <option value="6">6</option>
                  <option value="12">12</option>
                  <option value="24">24</option>
                  <option value="36">36</option>
                  <option value="48">48</option>
                </select>
                Bulan
              </div>
            </td>
          </tr>
          <tr>
            <td>Bunga Bulanan</td>
            <td>:</td>
            <td></td>
            <td>
              <div>
                <!-- <input type="text" id="simulator_interest_monthly" name="simulator_interest_monthly" value="0" size="70" style="width:35px" />  -->
                <select name="simulator_interest_monthly" id="simulator_interest_monthly">
                  <!-- <?php foreach ($list_productcode as $month) : ?> -->
                  <!-- <option value="<?= $month['bunga'] ?>"><?= $month['bunga'] ?></option> -->
                  <!-- <?php endforeach; ?> -->
                  <option value="0.99">0.99</option>
                </select>
                % | *Gunakan Titik (.) untuk Desimal
              </div>
            </td>
          </tr>
          <tr class="hide">
            <td>Bunga Tahunan</td>
            <td>:</td>
            <td></td>
            <td>
              <div> <input type="text" id="simulator_interest" name="simulator_interest" value="0" size="70" style="width:35px" /> % | *Gunakan Titik (.) untuk Desimal</div>
            </td>
          </tr>
          <tr>
            <td>Cicilan dengan Bunga Bulanan</td>
            <td>:</td>
            <td>Rp.</td>
            <td>
              <div> <input type="text" id="simulator_installment_monthly" name="simulator_installment_monthly" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
            </td>
          </tr>
          <tr>
            <td>EBR</td>
            <td>:</td>
            <td>Rp.</td>
            <td>
              <div> <input type="text" id="simulator_installment_ebr" name="simulator_installment_ebr" placeholder="EBR" size="70" style="width:300px" readonly=true /> </div>
            </td>
          </tr>
          <tr class="hide">
            <td>Cicilan dengan Bunga Tahunan</td>
            <td>:</td>
            <td>Rp.</td>
            <td>
              <div> <input type="text" id="simulator_installment" name="simulator_installment" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
            </td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <input type="button" class="btn-contacted" id="btn-hitung_simulator" value="Hitung" onclick="simulateInstallments()" /> &nbsp;
              <input type="button" class="btn-contacted" id="btn-reset_simulator" value="Reset" onclick="resetInstallments()" /> &nbsp;
              <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide')" />
            </td>
          </tr>
        </table>
        <br />
      </fieldset>
      <fieldset class="hide">
        <legend>Installment Tree</legend>
        <table style="overflow: auto; width: 100%">
          <?php foreach ($list_productcode as $prodcode) : ?>
            <?php
            if (empty($refreshbag)) {
              $pinjaman = $prospect['max_loan'] * 1;
            } else {
              $pinjaman = $refreshbag['max_loan'] * 1;
            }

            $tenor = $prodcode['tenor'] * 1;
            $cicilan_pokok = $pinjaman / $tenor;
            $cicilan_bunga = $pinjaman * ($prodcode['bunga'] / 100);
            $jml_angsuran = round($cicilan_pokok + $cicilan_bunga);
            ?>
            <tr>
              <td style="width:175px"> <?= $prodcode['tenor'] ?> Bulan - <?= $prodcode['bunga'] ?>%</td>
              <!-- <td style="width:175px"><?= $prodcode['status'] ?> - <?= $prodcode['tenor'] ?> Bulan - <?= $prodcode['bunga'] ?>%</td> -->
              <td>:</td>
              <td style="width:10px">Rp. </td>
              <td><?= price_format($jml_angsuran); ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </fieldset>
    </div>
    <script type="text/javascript">
      function simulateInstallments() {
        var loan = $('#simulator_sum2').val() * 1;
        var tenure = $('#simulator_tenure').val() * 1;
        var interest = $('#simulator_interest').val() * 1;
        var interest_mothly = $('#simulator_interest_monthly').val() * 1;
        //var installment  = $('#simulator_installment').val();
        var cicilan_pokok = loan / tenure;
        var cicilan_bunga = loan * (interest_mothly / 100);
        var jml_angsuran = cicilan_pokok + cicilan_bunga;

        interest = loan * ((interest / 12) / 100);
        var principal = (loan / tenure) * 1;
        var installment = (principal + interest) * 1;

        // EBR
        var cicilan_asli = loan * (tenure + 1);
        var cicilan_bung = cicilan_asli / 2;
        var hasil = cicilan_bung * (interest_mothly / 100);

        $('#simulator_installment').val(Math.round(installment));
        $('#simulator_installment').priceFormat({
          prefix: '',
          thousandsSeparator: ',',
          centsLimit: 0
        });

        $('#simulator_installment_monthly').val(Math.round(jml_angsuran));
        $('#simulator_installment_monthly').priceFormat({
          prefix: '',
          thousandsSeparator: ',',
          centsLimit: 0
        });

        $('#simulator_installment_ebr').val(Math.round(hasil));
        $('#simulator_installment_ebr').priceFormat({
          prefix: '',
          thousandsSeparator: ',',
          centsLimit: 0
        });
      }

      function resetInstallments() {
        $('#simulator_sum2').val(0);
        $('#simulator_sum3').val(0);
        $('#simulator_tenure').val(0);
        $('#simulator_installment').val(0);
        $('#simulator_interest').val(0);
        $('#simulator_interest_monthly').val(0);
        $('#simulator_installment_monthly').val(0);
      }

      function hitungkomas() {
        $('#simulator_sum3').priceFormat({
          prefix: '',
          thousandsSeparator: ',',
          centsLimit: 0
        });
        var loan1 = $('#simulator_sum3').val();
        $('#simulator_sum2').val(loan1);
        $('#simulator_sum2').priceFormat({
          prefix: '',
          thousandsSeparator: '',
          centsLimit: 0
        });

        //alert(sum);
        //var loan = $('#simulator_sum').val(loan1);
        //alert(loan);
        //$('#simulator_sum').val(Math.round(loan1)); 

        simulateInstallments();
      }

      function autofillSimulator() {
        <?php if (empty($refreshbag)) : ?>
          var nominal = "<?= @$prospect['available_credit']; ?>";
        <?php else : ?>
          var nominal = "<?= @$refreshbag['available_credit']; ?>";
        <?php endif; ?>
        var tenor = 6;
        var bunga = 0.89;

        $('#simulator_sum2').val(nominal);
        $('#simulator_sum3').val(nominal);
        $('#simulator_tenure').val(tenor);
        $('#simulator_interest_monthly').val(bunga);

        simulateInstallments();
      }
    </script>
  <?php endif; ?>
<?php } else if ($prospect['multiproduct'] == '1') { ?>
  <?php
  $product_xsell = json_decode($offers[0]['xsell_cardxsell'], true); //var_dump($product_xsell); exit();
  foreach ($product_xsell as $xsell) {
    if ($xsell == 'FP') :
  ?>
      <div id="installment_simulator_<?= $xsell; ?>">
        <fieldset>
          <legend>Installment Simulator Flexipay</legend>
          <table style="overflow: auto; width: 100%">
            <tr>
              <td>Nominal</td>
              <td>:</td>
              <td style="width:5px">Rp.</td>
              <td>
                <div style="font-size: 14px;">
                  <input type="number" min="500000" step="500000" id="simulator_plafon_fop" name="simulator_plafon_fop" value="500000" style="width:150px; font-size: 14px;" />
                </div>
              </td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td colspan="2" style="">
                <input type="button" style="width: 100px; margin-left: 28px;" class="btn-contacted" id="" value="Hitung Cicilan" onclick="simulasiPinjaman1_fop()">
                <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide','FP')" />
              </td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>Installment Tree</legend>
          <table style="overflow: auto; width: 100%">
            <tr>
              <td></td>
              <td colspan="2">
                <div id="result_simulator_fop" style="font-size: 13px;">
                  <?php $z = 0;
                  foreach ($list_productcode_FP as $prodcode) { ?>
                    <label><?= $prodcode['segment'] ?>/ <?= $prodcode['bunga'] ?>% / <?= $prodcode['tenor'] ?> Bulan / Bunga Efektif <?= $prodcode['bunga_efektif'] * 100 ?>% / Cicilan: <span id="angsuranCal_fop_<?= $z; ?>"></span>/ EBR: <span id="angsuranCal_ebr_<?= $z; ?>"></span> </label></br><br>
                    <!-- <label> Flexipay <?= $prodcode['status'] ?>/ <?= $prodcode['bunga'] ?>% / <?= $prodcode['tenor'] ?> Bulan / Bunga Efektif <?= $prodcode['bunga_efektif'] * 100 ?>% / Cicilan: <span id="angsuranCal_fop_<?= $z; ?>"></span>/ EBR: <span id="angsuranCal_ebr_<?= $z; ?>"></span> </label></br><br> -->
                    <input type="hidden" id="bunga_fop_<?= $z; ?>" value="<?= $prodcode['bunga']; ?>">
                    <input type="hidden" id="tenor_fop_<?= $z; ?>" value="<?= $prodcode['tenor']; ?>">
                  <?php $z++;
                  } ?>
                </div>
              </td>
            </tr>
          </table>
          <br />
        </fieldset>
      </div>
      <!-- Simulasi Flexipay -->
      <script type="text/javascript">
        function fixInputCal_fop() {
          var input_pinjaman = $('#simulator_plafon_fop').val();
          var max_pinjaman = 100000000;
          var fix_pinjaman;
          fix_pinjaman = input_pinjaman.replace(/[^0-9]/g, '');
          fix_pinjaman = parseInt(fix_pinjaman);

          var min_pinjaman = 500000;

          if (isNaN(fix_pinjaman)) {
            new Boxy.alert('Mohon Nominal tidak diisi dengan huruf, simbol atau tanda baca apapun, termasuk koma (,) dan Titik (.)');
            $('#ben_pinjamincome').val('0');
          } else if (input_pinjaman < min_pinjaman) {
            new Boxy.alert('Nominal terlalu kecil. Minimal : 500.000');
            $('#simulator_plafon_fop').val(min_pinjaman);
          } else {
            $('#simulator_plafon_fop').val(fix_pinjaman);
          }

        }

        function simulasiPinjaman1_fop() {
          fixInputCal_fop();
          var pinjaman = $('#simulator_plafon_fop').val() * 1;

          for (var i = 0; i < 14; i++) {
            var bunga = $('#bunga_fop_' + i).val() * 1;
            var tenor = $('#tenor_fop_' + i).val() * 1;
            var cicilan_pokok = pinjaman / tenor;
            var cicilan_bunga = pinjaman * (bunga / 100);
            var jml_angsuran = Math.round(cicilan_pokok + cicilan_bunga);

            // Script New
            var cicilan_asli = pinjaman * (tenor + 1);
            var cicilan_bung = cicilan_asli / 2;
            var hasil = Math.round(cicilan_bung * (bunga / 100));

            $('#angsuranCal_fop_' + i).empty();
            $('#angsuranCal_fop_' + i).append(jml_angsuran);

            $('#angsuranCal_fop_' + i).priceFormat({
              prefix: 'Rp.',
              thousandsSeparator: ',',
              centsLimit: 0
            });

            $('#angsuranCal_ebr_' + i).empty();
            $('#angsuranCal_ebr_' + i).append(hasil);
            $('#angsuranCal_ebr_' + i).priceFormat({
              prefix: 'Rp.',
              thousandsSeparator: ',',
              centsLimit: 0
            });
          }
        }

        $(document).ready(function() {
          simulasiPinjaman1_fop();
        });
      </script>
    <?php elseif ($xsell == 'COP') : ?>
      <div id="installment_simulator_<?= $xsell; ?>" style="height: auto;">
        <fieldset>
          <legend>Installment Simulator COP</legend>
          <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
          <table style="overflow: auto; width: 100%">
            <tr>
              <td style="width:175px">Nominal</td>
              <td>:</td>
              <td style="width:10px">Rp.</td>
              <td>
                <div> <input type="text" id="simulator_sum" name="simulator_sum" value="0" size="70" style="width:300px" /> </div>
              </td>
            </tr>
            <tr>
              <td>Tenor</td>
              <td>:</td>
              <td></td>
              <td>
                <div> <input type="text" id="simulator_tenure" name="simulator_tenure" value="0" size="70" style="width:35px" /> Bulan </div>
              </td>
            </tr>
            <tr>
              <td>Bunga Bulanan</td>
              <td>:</td>
              <td></td>
              <td>
                <div> <input type="text" id="simulator_interest_monthly" name="simulator_interest_monthly" value="0" size="70" style="width:35px" /> % | *Gunakan Titik (.) untuk Desimal</div>
              </td>
            </tr>
            <tr class="hide">
              <td>Bunga Tahunan</td>
              <td>:</td>
              <td></td>
              <td>
                <div> <input type="text" id="simulator_interest" name="simulator_interest" value="0" size="70" style="width:35px" /> % | *Gunakan Titik (.) untuk Desimal</div>
              </td>
            </tr>
            <tr>
              <td>Cicilan dengan Bunga Bulanan</td>
              <td>:</td>
              <td>Rp.</td>
              <td>
                <div> <input type="text" id="simulator_installment_monthly" name="simulator_installment_monthly" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
              </td>
            </tr>
            <tr>
              <td>EBR</td>
              <td>:</td>
              <td>Rp.</td>
              <td>
                <div> <input type="text" id="simulator_installment_ebr" name="simulator_installment_ebr" placeholder="EBR" size="70" style="width:300px" readonly=true /> </div>
              </td>
            </tr>
            <tr class="hide">
              <td>Cicilan dengan Bunga Tahunan</td>
              <td>:</td>
              <td>Rp.</td>
              <td>
                <div> <input type="text" id="simulator_installment" name="simulator_installment" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
              </td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td>
                <input type="button" class="btn-contacted" id="btn-hitung_simulator" value="Hitung" onclick="simulateInstallment()" /> &nbsp;
                <input type="button" class="btn-contacted" id="btn-reset_simulator" value="Reset" onclick="resetInstallment()" /> &nbsp;
                <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide', '<?= $xsell; ?>')" />
              </td>
            </tr>
          </table>
          <br />
        </fieldset>
        <fieldset>
          <legend>Installment Tree</legend>
          <table style="overflow: auto; width: 100%">
            <?php foreach ($list_productcode_cop as $prodcode) : ?>
              <?php
              // refresh bag cop
              if(!empty($refreshbagcop)){
                $pinjaman = $refreshbagcop['max_loan'] * 1;
              }else if (!empty($refreshbag) && !in_array("CPIL", json_decode($offers[0]['xsell_cardxsell']))) {
                $pinjaman = $refreshbag['max_loan'] * 1;
              } else {
                $pinjaman = $prospect['max_loan'] * 1;
              }

              $tenor = $prodcode['tenor'] * 1;
              $cicilan_pokok = $pinjaman / $tenor;
              $cicilan_bunga = $pinjaman * ($prodcode['bunga'] / 100);
              $jml_angsuran = round($cicilan_pokok + $cicilan_bunga);

              $cicilan_asli = $pinjaman * ($prodcode['tenor'] + 1);
              $cicilan_bung = $cicilan_asli / 2;
              $hasil = round($cicilan_bung * ($prodcode['bunga'] / 100));

              ?>
              <tr>
                <td style="width:320px; font-size:13px;"><?= $prodcode['status'] ?> - Bunga Efektif <?= @$prodcode['bunga_efektif'] * 100 ?>% - <?= $prodcode['tenor'] ?> Bulan - <?= $prodcode['bunga'] ?>%</td>
                <td>:</td>
                <td style="width:10px; font-size: 13px;">Rp. </td>
                <td style="font-size:13px; font-weight: bold"><?= price_format($jml_angsuran); ?></td>
                <td>EBR : </td>
                <td style="width:10px; font-size: 13px;">Rp. </td>
                <td style="font-size:13px; font-weight: bold"><?= price_format($hasil); ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        </fieldset>
      </div>
    <?php elseif ($xsell == 'CPIL') : ?>
        <div id="installment_simulator" style="height: auto;">
          <fieldset>
          <legend>Installment Simulator CPIL</legend>
          <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
          <table style="overflow: auto; width: 100%">
            <tr>
              <td style="width:175px">Nominal</td>
              <td>:</td>
              <td style="width:10px">Rp.</td>
              <!-- <td>
                <div> -->
              <!-- <input type="text" id="simulator_sum" name="simulator_sum" value="0" size="70" style="width:300px" /> -->
              <!-- <input type="text" id="simulator_sum" name="simulator_sum" value="0" size="70" style="width:300px" /> -->
              <!-- <input type="text" id="simulator_sum" name="simulator_sum" oninput="titikOtomatis(this)" value="0" size="70" style="width:300px" /> -->

              <!-- </div>
              </td> -->
              <td>
                <div> <input type="text" id="simulator_sum3_cpilxcop" name="simulator_sum3_cpilxcop" onchange="hitungkomas_cpilxcop()" value="0" size="70" style="width:300px" /> </div>
              </td>
              <td style="display: none;">
                <div> <input type="text" id="simulator_sum2_cpilxcop" name="simulator_sum2_cpilxcop" value="0" size="70" style="width:300px" /> </div>
              </td>
            </tr>
            <tr>
              <td>Tenor</td>
              <td>:</td>
              <td></td>
              <td>
                <div>
                  <!-- <input type="text" id="simulator_tenure_cpilxcop" name="simulator_tenure_cpilxcop" value="0" size="70" style="width:35px" /> -->
                  <select name="simulator_tenure_cpilxcop" id="simulator_tenure_cpilxcop">
                    <!-- <?php foreach ($list_productcode as $teno) : ?> -->
                    <!-- <option value="<?= $teno['tenor'] ?>"><?= $teno['tenor'] ?></option> -->
                    <!-- <?php endforeach; ?> -->
                    <option value="3">3</option>
                    <option value="6">6</option>
                    <option value="12">12</option>
                    <option value="24">24</option>
                    <option value="36">36</option>
                    <option value="48">48</option>
                  </select>
                  Bulan
                </div>
              </td>
            </tr>
            <tr>
              <td>Bunga Bulanan</td>
              <td>:</td>
              <td></td>
              <td>
                <div>
                  <!-- <input type="text" id="simulator_interest_monthly_cpilxcop" name="simulator_interest_monthly_cpilxcop" value="0" size="70" style="width:35px" />  -->
                  <select name="simulator_interest_monthly_cpilxcop" id="simulator_interest_monthly_cpilxcop">
                    <!-- <?php foreach ($list_productcode as $month) : ?> -->
                    <!-- <option value="<?= $month['bunga'] ?>"><?= $month['bunga'] ?></option> -->
                    <!-- <?php endforeach; ?> -->
                    <option value="0.49">0.49</option>
                    <option value="0.59">0.59</option>
                    <option value="0.69">0.69</option>
                    <option value="0.79">0.79</option>
                    <option value="0.89">0.89</option>
                    <option value="0.99">0.99</option>
                    <option value="1.15">1.15</option>
                    <option value="1.25">1.25</option>
                    <option value="1.30">1.30</option>
                    <option value="1.50">1.50</option>
                    <option value="1.59">1.59</option>
                    <option value="1.68">1.68</option>
                  </select>
                  % | *Gunakan Titik (.) untuk Desimal
                </div>
              </td>
            </tr>
            <tr class="hide">
              <td>Bunga Tahunan</td>
              <td>:</td>
              <td></td>
              <td>
                <div> <input type="text" id="simulator_interest_cpilxcop" name="simulator_interest_cpilxcop" value="0" size="70" style="width:35px" /> % | *Gunakan Titik (.) untuk Desimal</div>
              </td>
            </tr>
            <tr>
              <td>Cicilan dengan Bunga Bulanan</td>
              <td>:</td>
              <td>Rp.</td>
              <td>
                <div> <input type="text" id="simulator_installment_monthly_cpilxcop" name="simulator_installment_monthly_cpilxcop" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
              </td>
            </tr>
            <tr>
              <td>EBR</td>
              <td>:</td>
              <td>Rp.</td>
              <td>
                <div> <input type="text" id="simulator_installment_ebr_cpilxcop" name="simulator_installment_ebr_cpilxcop" placeholder="EBR" size="70" style="width:300px" readonly=true /> </div>
              </td>
            </tr>
            <tr class="hide">
              <td>Cicilan dengan Bunga Tahunan</td>
              <td>:</td>
              <td>Rp.</td>
              <td>
                <div> <input type="text" id="simulator_installment_cpilxcop" name="simulator_installment_cpilxcop" placeholder="Cicilan" size="70" style="width:300px" readonly=true /> </div>
              </td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td>
                <input type="button" class="btn-contacted" id="btn-hitung_simulator_cpilxcop" value="Hitung" onclick="simulateInstallments_cpilxcop()" /> &nbsp;
                <input type="button" class="btn-contacted" id="btn-reset_simulator_cpilxcop" value="Reset" onclick="resetInstallments_cpilxcop()" /> &nbsp;
                <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide', 'CPIL')" />
              </td>
            </tr>
          </table>
          <br />
        </fieldset>
        <fieldset class="hide">
          <legend>Installment Tree</legend>
          <table style="overflow: auto; width: 100%">
            <?php foreach ($list_productcode as $prodcode) : ?>
              <?php
              if (empty($refreshbag)) {
                $pinjaman = $prospect['max_loan'] * 1;
              } else {
                $pinjaman = $refreshbag['max_loan'] * 1;
              }

              $tenor = $prodcode['tenor'] * 1;
              $cicilan_pokok = $pinjaman / $tenor;
              $cicilan_bunga = $pinjaman * ($prodcode['bunga'] / 100);
              $jml_angsuran = round($cicilan_pokok + $cicilan_bunga);
              ?>
              <tr>
                <td style="width:175px"> <?= $prodcode['tenor'] ?> Bulan - <?= $prodcode['bunga'] ?>%</td>
                <!-- <td style="width:175px"><?= $prodcode['status'] ?> - <?= $prodcode['tenor'] ?> Bulan - <?= $prodcode['bunga'] ?>%</td> -->
                <td>:</td>
                <td style="width:10px">Rp. </td>
                <td><?= price_format($jml_angsuran); ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        </fieldset>
      </div>
      <script type="text/javascript">
        function simulateInstallments_cpilxcop() {
          var loan = $('#simulator_sum2_cpilxcop').val() * 1;
          var tenure = $('#simulator_tenure_cpilxcop').val() * 1;
          var interest = $('#simulator_interest_cpilxcop').val() * 1;
          var interest_mothly = $('#simulator_interest_monthly_cpilxcop').val() * 1;
          //var installment  = $('#simulator_installment_cpilxcop').val();
          var cicilan_pokok = loan / tenure;
          var cicilan_bunga = loan * (interest_mothly / 100);
          var jml_angsuran = cicilan_pokok + cicilan_bunga;

          interest = loan * ((interest / 12) / 100);
          var principal = (loan / tenure) * 1;
          var installment = (principal + interest) * 1;

          // EBR
          var cicilan_asli = loan * (tenure + 1);
          var cicilan_bung = cicilan_asli / 2;
          var hasil = cicilan_bung * (interest_mothly / 100);

          $('#simulator_installment_cpilxcop').val(Math.round(installment));
          $('#simulator_installment_cpilxcop').priceFormat({
            prefix: '',
            thousandsSeparator: ',',
            centsLimit: 0
          });

          $('#simulator_installment_monthly_cpilxcop').val(Math.round(jml_angsuran));
          $('#simulator_installment_monthly_cpilxcop').priceFormat({
            prefix: '',
            thousandsSeparator: ',',
            centsLimit: 0
          });

          $('#simulator_installment_ebr_cpilxcop').val(Math.round(hasil));
          $('#simulator_installment_ebr_cpilxcop').priceFormat({
            prefix: '',
            thousandsSeparator: ',',
            centsLimit: 0
          });
        }

        function resetInstallments_cpilxcop() {
          $('#simulator_sum2_cpilxcop').val(0);
          $('#simulator_sum3_cpilxcop').val(0);
          $('#simulator_tenure_cpilxcop').val(0);
          $('#simulator_installment_cpilxcop').val(0);
          $('#simulator_interest_cpilxcop').val(0);
          $('#simulator_interest_monthly_cpilxcop').val(0);
          $('#simulator_installment_monthly_cpilxcop').val(0);
        }

        function hitungkomas_cpilxcop() {
          $('#simulator_sum3_cpilxcop').priceFormat({
            prefix: '',
            thousandsSeparator: ',',
            centsLimit: 0
          });
          var loan1 = $('#simulator_sum3_cpilxcop').val();
          $('#simulator_sum2_cpilxcop').val(loan1);
          $('#simulator_sum2_cpilxcop').priceFormat({
            prefix: '',
            thousandsSeparator: '',
            centsLimit: 0
          });

          //alert(sum);
          //var loan = $('#simulator_sum').val(loan1);
          //alert(loan);
          //$('#simulator_sum').val(Math.round(loan1)); 

          simulateInstallments_cpilxcop();
        }

        function autofillSimulator_cpilxcop() {
          <?php if (empty($refreshbag)) : ?>
            var nominal = "<?= @$prospect['available_credit']; ?>";
          <?php else : ?>
            var nominal = "<?= @$refreshbag['available_credit']; ?>";
          <?php endif; ?>
          var tenor = 6;
          var bunga = 0.89;

          $('#simulator_sum2_cpilxcop').val(nominal);
          $('#simulator_sum3_cpilxcop').val(nominal);
          $('#simulator_tenure_cpilxcop').val(tenor);
          $('#simulator_interest_monthly_cpilxcop').val(bunga);

          simulateInstallments_cpilxcop();
        }
        autofillSimulator_cpilxcop();
    </script>
    <?php elseif ($xsell == 'PL') : ?>
      <div id="installment_simulator_<?= $xsell; ?>">
        <fieldset>
          <legend>Installment Simulator Personal Loan</legend>
          <!--<p> <input type="button" value="close" class="btn-uncontacted" onclick="zipcodelist('hide')" /></p>-->
          <table>
            <tr>
              <td>Nominal</td>
              <td>:</td>
              <td style="width:5px">Rp.</td>
              <td>
                <div style="font-size: 14px;">
                  <input type="number" min="5000000" step="500000" id="simulator_plafon" name="simulator_plafon" value="<?= $prospect['plafon12']; ?>" style="width:150px; font-size: 14px;" /> / Maksimal Pinjaman Rp<?php echo number_format($prospect['plafon12']) ?>
                </div>
              </td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td>
                <input type="button" style="width: 100px;" class="btn-contacted" id="" value="Hitung Cicilan" onclick="simulasiPinjaman1()">
                <input type="button" value="close" class="btn-uncontacted" onclick="installment_simulator('hide', '<?= $xsell; ?>')" />
              </td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>Installment Tree</legend>
          <table>
            <tr>
              <td></td>
              <td colspan="2">
                <div id="result_simulator" style="font-size: 13px;">
                  <?php $z = 0;
                  foreach ($list_productcode_PL as $prodcode) { ?>
                    <label> Personal Loan / <?= $prodcode['bunga'] ?>% / <?= $prodcode['tenor'] ?> Bulan / Bunga Efektif <?= $prodcode['bunga_efektif'] ?>% / Cicilan: <span id="angsuranCal_<?= $z; ?>"></span></label></br><br>
                    <input type="hidden" id="bunga_<?= $z; ?>" value="<?= $prodcode['bunga']; ?>">
                    <input type="hidden" id="tenor_<?= $z; ?>" value="<?= $prodcode['tenor']; ?>">
                  <?php $z++;
                  } ?>
                </div>
              </td>
            </tr>
          </table>
          <br />
        </fieldset>
      </div>
      <!-- Simulasi Personal Loan -->
      <script type="text/javascript">
        function fixInputCal() {
          var input_pinjaman = $('#simulator_plafon').val();
          var max_pinjaman = <?= $prospect['plafon12'] ?> * 1;
          var fix_pinjaman;
          fix_pinjaman = input_pinjaman.replace(/[^0-9]/g, '');
          fix_pinjaman = parseInt(fix_pinjaman);

          var min_pinjaman = 5000000;

          if (isNaN(fix_pinjaman)) {
            new Boxy.alert('Mohon Pinjaman tidak diisi dengan huruf, simbol atau tanda baca apapun, termasuk koma (,) dan Titik (.)');
            $('#ben_pinjamincome').val('0');
          } else if (input_pinjaman > max_pinjaman) {
            new Boxy.alert('Pinjaman melebihi limit dari tenor yang tersedia, mohon dicek kembali. ');
            $('#simulator_plafon').val(max_pinjaman);
          } else if (input_pinjaman < min_pinjaman) {
            new Boxy.alert('Pinjaman terlalu kecil. Minimal : 5.000.000');
            $('#simulator_plafon').val(min_pinjaman);
          } else {
            $('#simulator_plafon').val(fix_pinjaman);
          }
        }

        function simulasiPinjaman1() {
          fixInputCal();
          var pinjaman = $('#simulator_plafon').val() * 1;

          for (var i = 0; i < 4; i++) {
            var bunga = $('#bunga_' + i).val() * 1;
            var tenor = $('#tenor_' + i).val() * 1;
            var cicilan_pokok = pinjaman / tenor;
            var cicilan_bunga = pinjaman * (bunga / 100);
            var jml_angsuran = Math.round(cicilan_pokok + cicilan_bunga);

            $('#angsuranCal_' + i).empty();
            $('#angsuranCal_' + i).append(jml_angsuran);

            $('#angsuranCal_' + i).priceFormat({
              prefix: 'Rp.',
              thousandsSeparator: ',',
              centsLimit: 0
            });
          }
        }

        $(document).ready(function() {
          simulasiPinjaman1();
        });
      </script>
<?php endif;
  }
} ?>
<!-- End Simulator Cicilan -->

<?php if (@$prospect['is_priority'] == 2) { ?>
  <script type="text/javascript">
    $(document).ready(function() {
      //var url_get = 'https://192.168.116.254/smallscript/index.php/api/inbound_state/<?= $_SESSION["id_asterisk"] ?>/';
      //$.get(url_get, function(data, status){
      //var obj = JSON.parse(data);
      //var state = obj.result.state;
      //if (state != 'Down') {
      var url_sipcall = '<?= site_url() ?>/tsr/sip_call/';
      var incoming_number = '<?= $prospect['incoming_callerid']; ?>'; //obj.result.inbound.incoming_number;
      var inbound_uniqueid = '<?= @$prospect['inbound_uniqueid']; ?>'; //obj.result.inbound.incoming_id; 
      var value = incoming_number.replace("+62", "0");
      $('#no_contacted').val(value);
      var value2 = 9;
      doSipCallfirst2(url_sipcall, value, value2, inbound_uniqueid);
      //}
      //});
    });
  </script>
<?php } ?>


<!-- TSO COLOUMN 1 -->
<?php if (@$txt_result) : ?>
  <?php $confirmation = '<div style="width:200px">' . $txt_result . '</div>'; ?>
  <script type="text/javascript">
    showPopUp('Confirmation', '<?php echo $confirmation ?>');
  </script>
<?php endif; ?>
<script type="text/javascript">
  function IsNumeric(sText)

  {
    var ValidChars = "0123456789.";
    var IsNumber = true;
    var Char;

    var Str = "";
    for (i = 0; i < sText.length && IsNumber == true; i++) {
      Char = sText.charAt(i);
      if (ValidChars.indexOf(Char) == -1) {
        IsNumber = false;
      } else {
        IsNumber = true;
        Str += Char;
      }
    }
    return Str;

  }

  function unFormat(sText) {
    SS = "";
    for (i = 0; i < sText.length; i++) {
      Char = sText.charAt(i);
      if (Char != "." && Char != ",") {
        SS += Char;
      }
      if (Char == ",") {
        break;
      }
    }
    return SS;
  }

  $('#callcode_back2').click(function() {
    $('.det-right').hide();
    $('#subremis,#multi_subremis,#subpresent,#multi_offer').hide();
    $('#remis').slideDown('fast');
  });

  function showAdditionalInfo() {
    var xurl = "<?= site_url(); ?>ajax/tsr/get_additionalinfo";
    var dummy_id = '<?= @$prospect['dummy_id']; ?>';

    $.ajax({
      url: xurl,
      type: 'POST',
      data: {
        dummy_id: dummy_id
      },
      success: function(html) {
        new Boxy(html, {
          title: 'Additional Customer Info',
          draggable: true
        });
      }
    });

  }


  function reqAddPhone() {
    var id_prospect = $('#id_prospect').val();
    var xurl = "<?= site_url(); ?>ajax/tsr/get_addphoneview/" + id_prospect;

    $.ajax({
      url: xurl,
      type: 'POST',
      success: function(html) {
        var addPhoneWindow = new Boxy(html, {
          title: 'Request New Phone',
          modal: true,
          draggable: true
        });
      }
    });
  }

  function sipCallWarning(msg) {
    Boxy.alert(msg);
    return;
  }

  function loadFormVerifikasi() {
    var id_prospect = $('#id_prospect').val();
    var xurl = "<?= site_url(); ?>ajax/tsr/get_verifikasiview/" + id_prospect;

    $.ajax({
      url: xurl,
      type: 'POST',
      success: function(html) {
        var addPhoneWindow = new Boxy(html, {
          title: 'Customer Verification',
          modal: true,
          draggable: true,
          unloadOnHide: true
        });
      }
    });
  }

  function zipcodelist(cmd) {
    if (cmd == 'show') {
      $('#zipcode_finder').animate({
        "right": "5px"
      });
    } else {
      $('#zipcode_finder').animate({
        "right": "-2000px"
      });
    }
  }

  $('#searchkey').keyup(function() {
    var inp = $(this).val();
    var obj = $(this);
    if (inp.length < 4) {
      return;
    }
    xradio = $('input[type="radio"]');
    $.each(xradio, function(key, objx) {
      var is_checked = $(objx).is(':checked');
      if (is_checked == true) {
        var inptype = $(objx).val();
        get_autocomplete(inp, inptype, obj);
      }
    });
  });

  function updAutoComplete() {
    $('#searchkey').keyup();
  }

  function get_autocomplete(inp, inptype, obj) {
    var xurl = "<?= site_url(); ?>autocomplete/find/zipcode_list";

    $.ajax({
      url: xurl,
      type: "POST",
      data: {
        inp: inp,
        inptype: inptype
      },
      success: function(response) {
        var json = $.parseJSON(response);
        setTimeout(function() {
          updateTable(json);
        }, 500);
      }
    });
  }

  function updateTable(json) {
    $('#areaTable').empty();
    $.each(json, function(key, result) {
      var row = "<tr>";
      row += '<td class="td1">' + result.idx + '</td>';
      row += '<td class="td2">' + result.kota + '</td>';
      row += '<td class="td3">' + result.kecamatan + '</td>';
      row += '<td class="td4">' + result.kelurahan + '</td>';
      row += '<td class="td5">' + result.zipcode + '</td>';
      row += '<td class="td6">' + result.zone + '</td>';
      row += '</tr>';
      $('#areaTable').append(row);
    });
  }

  function installment_simulator(cmd, product = '') {
    if (cmd == 'show') {
      if (product == 'FP') {
        $('#installment_simulator_FP').animate({
          "right": "5px"
        });
        $('#installment_simulator_COP').animate({
          "right": "-2000px"
        });
        $('#installment_simulator_PL').animate({
          "right": "-2000px"
        });
      } else if (product == 'COP') {
        $('#installment_simulator_COP').animate({
          "right": "5px"
        });
        $('#installment_simulator_FP').animate({
          "right": "-2000px"
        });
      } else if (product == 'PL') {
        $('#installment_simulator_PL').animate({
          "right": "5px"
        });
        $('#installment_simulator_FP').animate({
          "right": "-2000px"
        });
      } else {
        $('#installment_simulator').animate({
          "right": "5px"
        });
      }
    } else {
      if (product == 'FP') {
        $('#installment_simulator_FP, #installment_simulator_COP').animate({
          "right": "-2000px"
        });
      } else if (product == 'COP') {
        $('#installment_simulator_COP, #installment_simulator_FP').animate({
          "right": "-2000px"
        });
      } else if (product == 'PL') {
        $('#installment_simulator_PL, #installment_simulator_FP').animate({
          "right": "-2000px"
        });
      } else {
        $('#installment_simulator').animate({
          "right": "-2000px"
        });
      }

    }
  }

  function simulateInstallment() {
    var loan = $('#simulator_sum').val() * 1;
    var tenure = $('#simulator_tenure').val() * 1;
    var interest = $('#simulator_interest').val() * 1;
    var interest_mothly = $('#simulator_interest_monthly').val() * 1;
    //var installment  = $('#simulator_installment').val();
    var cicilan_pokok = loan / tenure;
    var cicilan_bunga = loan * (interest_mothly / 100);
    var jml_angsuran = cicilan_pokok + cicilan_bunga;

    interest = loan * ((interest / 12) / 100);
    var principal = (loan / tenure) * 1;
    var installment = (principal + interest) * 1;

    // EBR
    var cicilan_asli = loan * (tenure + 1);
    var cicilan_bung = cicilan_asli / 2;
    var hasil = cicilan_bung * (interest_mothly / 100);

    $('#simulator_installment').val(Math.round(installment));
    $('#simulator_installment').priceFormat({
      prefix: '',
      thousandsSeparator: ',',
      centsLimit: 0
    });

    $('#simulator_installment_monthly').val(Math.round(jml_angsuran));
    $('#simulator_installment_monthly').priceFormat({
      prefix: '',
      thousandsSeparator: ',',
      centsLimit: 0
    });

    $('#simulator_installment_ebr').val(Math.round(hasil));
    $('#simulator_installment_ebr').priceFormat({
      prefix: '',
      thousandsSeparator: ',',
      centsLimit: 0
    });
  }

  function resetInstallment() {
    $('#simulator_sum').val(0);
    $('#simulator_sum1').val(0);
    $('#simulator_tenure').val(0);
    $('#simulator_installment').val(0);
    $('#simulator_interest').val(0);
    $('#simulator_interest_monthly').val(0);
    $('#simulator_installment_monthly').val(0);
  }

  function parsexsell(xsellid) {
    $('#xsell').val(xsellid);
  }

  <?php if ($prospect['is_priority'] == 2) { ?>

    function go_disconnected() {
      var msg = '<p>Telp Nasabah akan dinyatakan Terputus, yakin ?</p>';
      var url = "<?= site_url() ?>tsr/set_disconected";
      new Boxy.confirm(msg, function() {
        set_disconected();
      });

      function set_disconected() {
        jQuery.post(url, {
            username: jQuery('#username').val(),
            id_user: jQuery('#id_user').val(),
            id_spv: jQuery('#id_spv').val(),
            id_tsm: jQuery('#id_tsm').val(),
            id_prospect: jQuery('#id_prospect').val(),
            id_product: jQuery('#id_product').val(),
            id_campaign: jQuery('#id_campaign_hid').val(),
            id_calltrack: jQuery('#id_calltrack').val(),
            id_callcode: jQuery('#id_callcode').val(),
            call_attempt: jQuery('#call_attempt').val(),
            remark: jQuery('#remark').val(),
            post: true
          },
          function(redirect) {
            location.href = redirect;
          });
      }
    }

  <?php } ?>

  function hitungkoma() {
    $('#simulator_sum1').priceFormat({
      prefix: '',
      thousandsSeparator: ',',
      centsLimit: 0
    });
    var loan1 = $('#simulator_sum1').val();
    $('#simulator_sum').val(loan1);
    $('#simulator_sum').priceFormat({
      prefix: '',
      thousandsSeparator: '',
      centsLimit: 0
    });

    //alert(sum);
    //var loan = $('#simulator_sum').val(loan1);
    //alert(loan);
    //$('#simulator_sum').val(Math.round(loan1)); 

    simulateInstallment();
  }
</script>