<style>
	input,
	textarea,
	select {
		border-radius: 5px;
		padding-left: 2px;
	}

	.smallInfo {
		font-size: 8px;
	}

	.cmpInfo {
		color: #228B22;
	}

	.formContainer {
		border: 0px red solid;
	}

	.smallInput {
		width: 50px;
	}

	.break {
		background-color: gray;
	}

	.formTab {
		padding: 5px;
		width: 80px;
		border: 1px solid gray;
		border-radius: 10px 10px 0px 0px;
		float: left;
		background-color: gray;
		color: #FFF;
	}

	.formTab:hover {
		text-shadow: 2px 2px 3px #FFD700;
		cursor: pointer;
	}

	textarea {
		width: 200px;
		height: 80px;
	}

	input[readonly],
	textarea[readonly] {
		background-color: #bfbfbf;
	}

	.angsuranread {
		color: green;
	}

	.confirmationBox {
		display: none;
	}

	.floatingRight {
		width: 600px;
		height: 450px;
		position: fixed;
		background: RGBA(0, 0, 0, 0.5);
		top: 10px;
		right: 10px;
		font-size: 1.1em;
		border-radius: 100px;
		box-shadow: 8px 8px 8px #FFFAF0;
		display: none;
		color: #FFF;
		font-weight: bold;
		border: 0px;
	}

	.floatingRight pre,
	.floatingRight p {
		padding: 30px;
		margin-top: 20px;
	}

	.floatingRight p {
		text-align: right;
		color: blue;
		text-decoration: underline;
	}

	.floatingRight h1 {
		color: Orange;
	}

	.floatingRight hr {
		margin-bottom: 20px;
	}
</style>

<script src="<?php echo base_url() ?>/component/js/agree_validation/<?= @$prospect_detail['campaign_product'] ?>-<?= @$prospect_detail['campaign_type'] ?>.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>/component/js/terbilang.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		showForm('apl');
		colorize_requiredfield();
		hitungCicilan();
		autoFormat();
		checkPinjaman();
		
	});

	$('.floatingRight').click(function() {
		$(this).hide(300);
	});

	$(function() {
		$("#bio_dob").datepicker({
			showOn: 'button',
			buttonImage: '<?php echo base_url() ?>/component/images/ico-date.jpg',
			dateFormat: 'yy-mm-dd',
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true
		});
	});

	$(function() {
		$("#tgl_cycle").datepicker({
			showOn: 'button',
			buttonImage: '<?php echo base_url() ?>/component/images/ico-date.jpg',
			dateFormat: 'yy-mm-dd',
			buttonImageOnly: true,
			changeMonth: true,
			minDate: 'now',
			maxDate: '+30d',
			changeYear: true
		});
	});

	$(function() {
		$("#datepicker_supdob").datepicker({
			showOn: 'button',
			buttonImage: '<?php echo base_url() ?>/component/images/ico-date.jpg',
			dateFormat: 'yy-mm-dd',
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true
		});
	});

	/*
	  $(function() {
	  $("#pku_date").datepicker({showOn: 'button', buttonImage: '<?php echo base_url() ?>/component/images/ico-date.jpg',dateFormat: 'yy-mm-dd', buttonImageOnly: true, changeMonth: true, minDate: 'now', maxDate: '+7d',
	   changeYear: true});
	 });
	*/

	$(function() {
		$("#bio_socialexp").datepicker({
			showOn: 'button',
			buttonImage: '<?php echo base_url() ?>/component/images/ico-date.jpg',
			dateFormat: 'yy-mm-dd',
			buttonImageOnly: true,
			changeMonth: true,
			changeYear: true
		});
	});


	function formatThis(obj) {
		$(obj).priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
	}

	function autoFormat() {
		var xobj = $('[data-format="terbilang"]');
		$.each(xobj, function(x, y) {
			var xvalue = $(y).html();
			if (xvalue != '') {
				$(y).html(terbilang(xvalue)).css({
					color: "Maroon"
				});
			}
		});
	}

	function formControl(idx) {
		var formlist = new Array('-', 'apl', 'ben', 'pjm');
		if (idx >= formlist.length) {
			alert('End of tab reached !');
		} else if (idx < 1) {
			alert('Nothing to back !');
		} else {
			return formlist[idx];
		}
	}

	//Custom Tabbing -> Martin
	function showForm(type) {
		switch (type) {
			case 'apl':
				$('.formContainer').hide();
				$('#divAplikasi').fadeIn('slow');
				$('.formTab').css("text-shadow", "0px 0px 0px #000");
				$('.formTab').css("background", "gray");
				$('#' + type).css("text-shadow", "2px 2px 3px #FFD700");
				$('#' + type).css("background", "linear-gradient(white, gray)");
				//$('#submit_box').fadeOut();
				$('#curtab').val(1);
				break;

			case 'ben':
				$('.formContainer').hide();
				$('#frmBenefit').fadeIn('slow');
				$('.formTab').css("text-shadow", "0px 0px 0px #000");
				$('.formTab').css("background", "gray");
				$('#' + type).css("text-shadow", "2px 2px 3px #FFD700");
				$('#' + type).css("background", "linear-gradient(white, gray)");
				//$('#submit_box').fadeOut();
				$('#curtab').val(3);
				break;

			case 'pjm':
				$('.formContainer').hide();
				$('#frmPinjeman').fadeIn('slow');
				$('.formTab').css("text-shadow", "0px 0px 0px #000");
				$('.formTab').css("background", "gray");
				$('#' + type).css("text-shadow", "2px 2px 3px #FFD700");
				$('#' + type).css("background", "linear-gradient(white, gray)");
				//$('#submit_box').fadeOut();
				$('#curtab').val(4);
				break;

			default:
				$('.formContainer').hide();
				$('#divAplikasi').fadeIn('slow');
				$('.formTab').css("text-shadow", "0px 0px 0px #000");
				$('.formTab').css("background", "gray");
				$('#' + type).css("text-shadow", "2px 2px 3px #FFD700");
				$('#' + type).css("background", "linear-gradient(white, gray)");
				$('#submit_box').fadeOut();
				$('#curtab').val(1);
				break;
		}

	}

	$('#apl_msc').change(function() {
		$('#xtenor').val('0'); //reset selected tenor cache;
		$('#ben_bunga').val('0'); //reset selected bunga cache;
		$('#ben_cicilanincome').val('0'); //reset cicilan cache;
		$('#apl_productcode').val(''); //reset productcode cache;
		//$('#tujuan_pembiayaan').val(''); //reset productcode cache;

		var xsel = $(this).val();
		// var pinjaman = $('#ben_pinjamincome').val() * 1;
		var pinjaman = parseInt($('#ben_pinjamincome').val().replace(/,/g, ""), 10) * 1;
		var juta = 1000000;
		var adminfee = 0;
		var campaign_name = '<?= $prospect_detail['name'] ?>';

		// Hitung DAP
		if (campaign_name.search('DAP') >= 0) {
			if (pinjaman < 15 * juta) {
				var adminfee = 100000;
			} else if (pinjaman >= 15 * juta && pinjaman <= 30 * juta) {
				var adminfee = 200000;
			} else if (pinjaman > 30 * juta) {
				var adminfee = 300000;
			}
		}
		// onmra:
		if($('#apl_msc').val() == 'COP R-0'){
			// adminfee = 2/100 * ($('#ben_pinjamincome').val()  * 1 );
			adminfee = 2/100 * (parseInt($('#ben_pinjamincome').val().replace(/,/g, ""), 10) * 1 );
		}
		$('#ben_materai').val(adminfee);
		$('#ben_materai').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});

		if (xsel != '') {
			var url = "<?php echo site_url(); ?>ajax/tsr/get_mscdetail/" + xsel + "/";
			var campaign = "<?= $prospect_detail['campaign_type']; ?>";
			$.ajax({
				url: url,
				type: "GET",
				success: function(resp) {
					if (resp != 'ERR') {
						var prx = $.parseJSON(resp);
						$('#tenor_option').empty();
						$.each(prx, function(i, l) {
							var angsuran = simulasiPinjaman(pinjaman, l.bunga, l.tenor);
							//alert(campaign);
							if (campaign == '5') {
								$('#tenor_option').append('<p><input type="radio" id="ben_limiteditionopt' + i + '" name="ben_limiteditionopt" value="' + l.tenor + '" data-bunga="' + l.bunga + '"  onclick="parseBunga2(this)" /> ' + l.description + ' - <span id="angsuran_' + i + '" class="angsuranread" >' + angsuran + '</span></p>');
							} else {
								$('#tenor_option').append('<p><input type="radio" id="ben_limiteditionopt' + i + '" name="ben_limiteditionopt" value="' + l.tenor + '" data-bunga="' + l.bunga + '" data-productcode="' + l.product_code + '" onclick="parseBunga(this)" /> ' + l.description + ' - <span id="angsuran_' + i + '" class="angsuranread" >' + angsuran + '</span></p>');
							}
							$('#angsuran_' + i).priceFormat({
								prefix: 'Rp.',
								thousandsSeparator: ',',
								centsLimit: 0
							})
						});
					} else {
						$('#tenor_option').empty();
						$('#tenor_option').append('-Choose MSC-');
					}
				}
			});
		} else {
			$('#tenor_option').empty();
			$('#tenor_option').append('-Choose MSC-');
		}
	});


	function parseBunga(obj) {
		$('#ben_bunga').val($(obj).attr('data-bunga'));
		$('#apl_productcode').val($(obj).attr('data-productcode'));
		var xtenor = $(obj).val();
		$('#xtenor').val(xtenor);
		hitungCicilan(xtenor);
	}

	function hanyaAngka(event) {
		var angka = (event.which) ? event.which : event.keyCode
		if (angka != 46 && angka > 31 && (angka < 48 || angka > 57))
			return false;
		return true;
	}

	function parseBunga2(obj) {
		//alert('aaaaa');

		$('#ben_bunga').val($(obj).attr('data-bunga'));
		var xtenor = $(obj).val();
		$('#xtenor').val(xtenor);
		var msc = $('#apl_msc').val();
		var bunga = $('#ben_bunga').val();
		var tujuan = $('[name="ben_pinjamopt"]:checked').val();
		//alert(tujuan);
		//alert(ben_bunga+' '+xtenor+' '+tujuan);
		var url = "<?php echo site_url(); ?>ajax/tsr/get_msc/";
		//alert(url);
		var campaign = "<?= $prospect_detail['campaign_type']; ?>";
		if (campaign == '5') {
			$.ajax({
				url: url,
				data: {
					msc: msc,
					bunga: bunga,
					tenor: xtenor,
					tujuan: tujuan
				},
				type: "POST",
				success: function(resp) {
					//alert(resp);
					if (resp != 'ERR') {
						var json = $.parseJSON(resp);
						var parse = json[0].product_code;
						$('#apl_productcode').val(parse);
					}
				}
			});
		}
		hitungCicilan(xtenor);

	}

	function parseBunga3() {
		//alert('aaaaa');
		var limid = $('[name="ben_limiteditionopt"]:checked');
		var tujuan = $('[name="ben_pinjamopt"]:checked').val();
		var xsel = $('#apl_msc').val();
		// var pinjaman = $('#ben_pinjamincome').val() * 1;
		var pinjaman = parseInt($('#ben_pinjamincome').val().replace(/,/g, ""), 10) * 1;
		if (xsel != '') {
			var url = "<?php echo site_url(); ?>ajax/tsr/get_mscdetail1/" + xsel + "/" + tujuan + "/";
			var campaign = "<?= $prospect_detail['campaign_type']; ?>";
			//alert(campaign);
			if (campaign == '5') {
				$.ajax({
					url: url,
					type: "GET",
					success: function(resp) {
						//alert(resp);
						//return;
						if (resp != 'ERR') {
							var prx = $.parseJSON(resp);
							$('#tenor_option').empty();
							$.each(prx, function(i, l) {
								var angsuran = simulasiPinjaman(pinjaman, l.bunga, l.tenor);
								//alert(campaign);
								if (campaign == '5') {
									$('#tenor_option').append('<p><input type="radio" id="ben_limiteditionopt' + i + '" name="ben_limiteditionopt" value="' + l.tenor + '" data-bunga="' + l.bunga + '"  onclick="parseBunga2(this)" /> ' + l.description + ' - <span id="angsuran_' + i + '" class="angsuranread" >' + angsuran + '</span></p>');
								} else {
									$('#tenor_option').append('<p><input type="radio" id="ben_limiteditionopt' + i + '" name="ben_limiteditionopt" value="' + l.tenor + '" data-bunga="' + l.bunga + '" data-productcode="' + l.product_code + '" onclick="parseBunga(this)" /> ' + l.description + ' - <span id="angsuran_' + i + '" class="angsuranread" >' + angsuran + '</span></p>');
								}
								$('#angsuran_' + i).priceFormat({
									prefix: 'Rp.',
									thousandsSeparator: ',',
									centsLimit: 0
								})
							});
						} else {
							$('#tenor_option').empty();
							$('#tenor_option').append('-Choose MSC-');
						}
					}
				});
			}
		}
		parseBunga2(bunga);

	}

	function hitungCicilan() {
		fixInput();
		var bi_limit = 50; //percentage batas dari BI
		var min_pencairan = 1000000;
		var batas_pinjaman = $('#pjm_maxloan').val() * 1;
		var cardlimit = <?= @$prospect_detail['creditlimit']; ?> * 1;
		var availcredit = $('#available_credit').val() * 1;
		// var pinjaman = $('#ben_pinjamincome').val() * 1;
		var pinjaman = parseInt($('#ben_pinjamincome').val().replace(/,/g, ""), 10) * 1;
		var cop_flexibleamount = "<?= $cop_flexibleamount[0]['value'] ?>";

		if (pinjaman > batas_pinjaman) {
			pinjaman = batas_pinjaman;
		}

		// Hitung Pencairan dana
		var rdf = "<?= @$prospect_detail['rdf']; ?>";
		rdf == "" ? rdf = 0 : rdf;
		rdf = rdf * 1;
		if (rdf > 0 && cop_flexibleamount == "1") {
			var rdf_val = Math.round((rdf / 100) * cardlimit); //eligible EDF  
		} else {
			var rdf_val = batas_pinjaman;
		}


		rdf_val > batas_pinjaman ? rdf_val = batas_pinjaman : rdf_val;
		rdf_val = pembulatan(rdf_val, 100000) * 1;
		$('#rdf_test').html(rdf_val);

		if (pinjaman > rdf_val) {
			pinjaman = rdf_val;
		}

		var loan1_raw = 0;
		var loan2_raw = 0;
		var loan3_raw = 0;

		var loan1_step = 0;
		var loan2_step = 0;
		var loan3_step = 0;

		var loan1 = 0;
		var loan2 = 0;
		var loan3 = 0;

		loan1_raw = (bi_limit / 100) * availcredit;
		loan2_raw = (bi_limit / 100) * (availcredit - loan1_raw);
		loan3_raw = (bi_limit / 100) * (availcredit - loan1_raw - loan2_raw);

		loan1_step = pinjaman < loan1_raw ? pinjaman : loan1_raw;
		loan1_step = pembulatan(loan1_step, 100000);

		if (pinjaman < (loan1_raw + loan2_raw)) {
			loan2_step = pinjaman - loan1_step;
			loan2_step = loan2_step < min_pencairan ? 0 : Math.floor(loan2_step);
		} else {
			loan2_step = loan2_raw;
			loan2_step = loan2_step < min_pencairan ? 0 : Math.floor(loan2_step);
		}
		loan2_step = pembulatan(loan2_step, 100000);

		if (pinjaman < (loan1_raw + loan2_raw + loan3_raw)) {
			loan3_step = pinjaman - (loan1_step + loan2_step);
			loan3_step = loan3_step < min_pencairan ? 0 : Math.floor(loan3_step);
		} else {
			loan3_step = 0;
		}
		loan3_step = pembulatan(loan3_step, 100000);


		if (cop_flexibleamount != "1") {
			// loan1_step = $('#pjm_loan1').val();
			// loan2_step = $('#pjm_loan2').val();
			loan1_step = parseInt($('#pjm_loan1').val().replace(/,/g, ""), 10);
			loan2_step = parseInt($('#pjm_loan2').val().replace(/,/g, ""), 10);
			loan3_step = $('#pjm_loan3').val();
		}
		$('#loan1_test').html("RAW:" + loan1_raw + ' Step:' + loan1_step);
		$('#loan2_test').html("RAW:" + loan2_raw + ' Step:' + loan2_step);
		$('#loan3_test').html("RAW:" + loan3_raw + ' Step:' + loan3_step);

		$('#maxloan_read').html(rdf_val);
		$('#maxloan_read').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#max_loan_terbilang').html(terbilang(rdf_val));
		$('#pjm_maxloan').val(rdf_val);

		$('#loan1_read').html(loan1_step);
		$('#loan1_read').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#loan1_terbilang').html(terbilang(loan1_step));
		$('#pjm_loan1').val(loan1_step);
		$('#pjm_loan1').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#loan2_read').html(loan2_step);
		$('#loan2_read').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#loan2_terbilang').html(terbilang(loan2_step));
		$('#pjm_loan2').val(loan2_step);
		$('#pjm_loan2').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#loan3_read').html(loan3_step);
		$('#loan3_read').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#loan3_terbilang').html(terbilang(loan3_step));
		$('#pjm_loan3').val(loan3_step);

		console.log(loan1_step);
		console.log(loan2_step);
		console.log(loan3_step);
		var total_pencairan = (loan1_step + loan2_step + loan3_step) * 1;

		pinjaman = pembulatan(pinjaman, 100000);
		// alert(pinjaman);
		$('#ben_pinjamincome').val(pinjaman);
		$('#ben_pinjamincome').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		var bunga = parseFloat($('#ben_bunga').val()) * 1;
		//alert(bunga);
		//return;
		var tenor = $('#xtenor').val() * 1;
		var cicilan_pokok = pinjaman / tenor;
		var cicilan_bunga = pinjaman * (bunga / 100);
		var jml_angsuran = cicilan_pokok + cicilan_bunga;

		var provisi = ($('#ben_provisi').val() * 1 / 100); // tambahan martin. -> Nilai dalam persen %
		var outstanding = $('#ben_os').val() * 1; // tambahan martin.
		var hitung_provisi = pinjaman * provisi;
		//var materai = $('#ben_materai').val() * 1;
		var materai = 0; // tidak ada biaya materai di pencairan
		var penerimaan = pinjaman - (outstanding + hitung_provisi + materai);


		$('#ben_totalbunga').val(Math.round(cicilan_bunga * tenor));
		$('#ben_totalbunga').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#ben_totalbunga_terbilang').html(terbilang(Math.round(cicilan_bunga * tenor)));

		$('#ben_totalbungabulanan').val(Math.round(cicilan_bunga));
		$('#ben_totalbungabulanan').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#ben_totalbungabulanan_terbilang').html(terbilang(Math.round(cicilan_bunga)));


		$('#ben_totalbungaharian').val(Math.round((cicilan_bunga * 12) / 365));
		$('#ben_totalbungaharian').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});
		$('#ben_totalbungaharian_terbilang').html(terbilang(Math.round((cicilan_bunga * 12) / 365)));

		$('#ben_totalpenerimaandana').html(Math.round(penerimaan));
		$('#ben_totalpenerimaandana').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});

		$('#ben_cicilanincome').val(Math.round(jml_angsuran));
		$('#ben_cicilanincome').priceFormat({
			prefix: '',
			thousandsSeparator: ',',
			centsLimit: 0
		});

		// Terbilang
		$('#bilang_totalpenerimaandana').html(terbilang(Math.round(penerimaan)));
		$('#bilang_pinjamincome').html(terbilang(pinjaman));
		//alert(jml_angsuran);
		if (Math.round(jml_angsuran) > 0 && Math.round(jml_angsuran) < 100000000000000) {
			$('#bilang_cicilanincome').html(terbilang(Math.round(jml_angsuran)));
		}

		$('#ben_pinjamread').html($('#ben_pinjamincome').val());
		// $('#ben_pinjamread').priceFormat({
		// 	prefix: 'Rp.',
		// 	thousandsSeparator: ',',
		// 	centsLimit: 0
		// });

		$('#ben_osread').html($('#ben_os').val());
		$('#ben_osread').priceFormat({
			prefix: 'Rp.',
			thousandsSeparator: ',',
			centsLimit: 0
		});

		updateSimulasi();
	}

	function checkPinjaman() {
		// var pinjaman = $('#ben_pinjamincome').val() * 1;
		var pinjaman = parseInt($('#ben_pinjamincome').val().replace(/,/g, ""), 10) * 1;
		// var loan1 = $('#pjm_loan1').val() * 1;
		// var loan2 = $('#pjm_loan2').val() * 1;
		var loan1 = parseInt($('#pjm_loan1').val().replace(/,/g, ""), 10) * 1;
		var loan2 = parseInt($('#pjm_loan2').val().replace(/,/g, ""), 10) * 1;
		var loan3 = $('#pjm_loan3').val() * 1;
		var total_pencairan = loan1 + loan2 + loan3;
		if (pinjaman > total_pencairan) {
			pinjaman = total_pencairan;
		}
		
		// onmra:
		if($('#apl_msc').val() == 'COP R-0'){
			$('#ben_materai').val(2/100 * total_pencairan);
			$('#ben_materai').priceFormat({
				prefix: '',
				thousandsSeparator: ',',
				centsLimit: 0
			});
		}
		// console.log(total_pencairan);
		$('#ben_pinjamincome').val(total_pencairan);
		// $('#ben_pinjamincome').val(pinjaman);
		hitungCicilan();

	}

	function fixInput() {

	}

	function pembulatan(value, num) {
		return Math.floor(value / num) * num;
	}

	function simulasiPinjaman(pinjaman, bunga, tenor) {
		var pinjaman = pinjaman * 1;
		var bunga = bunga * 1;
		var tenor = tenor * 1;
		var cicilan_pokok = pinjaman / tenor;
		var cicilan_bunga = pinjaman * (bunga / 100);
		var jml_angsuran = Math.round(cicilan_pokok + cicilan_bunga);

		return jml_angsuran;
	}

	function updateSimulasi() {
		var tenoropt = $("input[id^='ben_limiteditionopt']");
		$.each(tenoropt, function(i, l) {
			// var pinjaman = $('#ben_pinjamincome').val() * 1;
			var pinjaman = parseInt($('#ben_pinjamincome').val().replace(/,/g, ""), 10) * 1;
			var tenor = (l.value) * 1;
			var bunga = ($(l).attr('data-bunga')) * 1
			var cicilan_pokok = pinjaman / tenor;
			var cicilan_bunga = pinjaman * (bunga / 100);
			var jml_angsuran = Math.round(cicilan_pokok + cicilan_bunga);
			$('#angsuran_' + i).html(jml_angsuran);
			$('#angsuran_' + i).priceFormat({
				prefix: 'Rp.',
				thousandsSeparator: ',',
				centsLimit: 0
			});
		});
	}


	$('#dosubmit').click(function() {
		var msc_select = $('#apl_msc').val();
		//var pickup_opt = $('[name="pickup_opt"]:checked').val();
		//var pickup_opt = $('#pickup_opt').val();
		var camp_type = "<?php echo $prospect_detail['campaign_type']; ?>";
		// Validasi untuk Submit
		//var econ_name = $('#fam_fullname').val();
		//var fam_homephonearea = $('#fam_homephonearea').val();
		//var fam_homephone = $('#fam_homephone').val();
		//var fam_fullhomephone = fam_homephonearea + fam_homephone;
		//var fam_cellular = $('#fam_cellular').val();
		var cicilan = $('#ben_cicilanincome').val();
		var tenor = $('#xtenor').val();
		var bunga = $('#ben_bunga').val();
		var pjm_accname = $('#pjm_accname').val();
		var pjm_accbranch = $('#pjm_accbranch').val();
		var pjm_listbank = $('#pjm_banknameother').val();
		var pjm = $('[name="pjm_bankopt"]:checked').val();
		var is_validated = $('#is_validated').val();

		if (is_validated != '1') {
			alert('Mohon lakukan verifikasi dulu sebelum melakukan submit agree!');
			return;
		}
		/*
				  if (pickup_opt == "EML"){
					if(pku_email==""){
						Boxy.alert('Email Wajib Diisi');
						return;
					}
					return;
				  }
				  else if (pku_email == ""){
					if(pku_email==""){
						Boxy.alert('Email Wajib Diisi');
						return;
					}else Boxy.alert('Email Wajib Diisi');
					return;
				  }
				  else */
		if (msc_select == "") {
			Boxy.alert('MSC Wajib dipilih');
			return;
		} else if (cicilan == '0') {
			Boxy.alert('Cicilan tidak terbaca, mohon periksa kembali tab "Fasilitas Pinjaman"');
			return;
		} else if (tenor == '0') {
			Boxy.alert('Tenor tidak terbaca, mohon periksa kembali tab "Fasilitas Pinjaman"');
			return;
		} else if (pjm_accname == '') {
			Boxy.alert('Nama Penerima di Pencairan rekening tidak terbaca!"');
			return;
		} else if (pjm == 'OTHER_BANK') {
			if (pjm_listbank == '') {
				Boxy.alert('Nama Bank tidak dipilih, Mohon Periksa Kembali tab "Data Pinjaman!"');
				return;
			}
		}
		/*          
		          else if(pjm_accbranch == ''){
		            Boxy.alert('Cabang Pembuka di Pencairan rekening tidak terbaca!"');
				  	return;
				  }
		*/
		var weekname_arr = Array('-', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu');
		var weekint = "<?php echo DATE('N'); ?>";
		var dayofweek = weekname_arr[weekint];

		var pinjaman = $('#ben_pinjamread').html();
		//var cicilan =  $('#ben_cicilanincome').val(); //-> dipindahin keatas sekalian validasi
		//var tenor =  $('#xtenor').val(); //-> dipindahin keatas sekalian validasi
		var namalengkap = $('#bio_fullname').val();

		$('#dayofweek').html(dayofweek);
		$('#conf_pinjaman').html(pinjaman);
		$('#conf_cicilan').html(cicilan);
		$('#conf_tenor').html(tenor);
		$('#conf_namalengkap').html(namalengkap);

		var html = $('#confirmationBox').html();
		var myWindow = new Boxy(html, {
			modal: true,
			title: "Confirmation",
			closetext: "[close]"
		});
		setTimeout(function() {
			myWindow.hideAndUnload();
		}, 5000); // close current window
	});

	function confirmationOk(obj) {
		var multiproduct = '<?= $multiproduct; ?>';
		$(obj).attr('disabled', 'disabled');

		if (multiproduct == '' || multiproduct == '0') {
			$('#frmAplikasi').submit(); // submit form normally   
		} else if (multiproduct == 'COP') {
			// submit via ajax
			var xurl = "<?= $submit1_url; ?>";
			$.ajax({
				url: xurl,
				type: "POST",
				data: $('#frmAplikasi').serialize(),
				success: function(resp) {
					$('[data-btncamptype^="COP"]').remove();
					$('#connect').html('<p>Submited Succesfully</p>');
					agreebtn_controller();
					$('#btn-next').show();
					var myWindow = new Boxy('<p>Please Wait..</p>', {
						modal: true,
						title: "Notice"
					});
					setTimeout(function() {
						myWindow.hideAndUnload();
					}, 2000);
				}
			});
		}
	}

	$('#docancel').click(function() {
		new Boxy.confirm('Formulir aplikasi dan isinya akan ditutup, proses ??', function() {
			$('#connect').hide();
			$('#remis').fadeIn();
		});
	});

	function nextTab() {
		var curtab = parseInt($('#curtab').val());
		var nexttab = curtab + 1;
		var tabname = formControl(nexttab);
		if (tabname.length > 1) {
			showForm(tabname);
		} else {
			return;
		}
	}

	function prevTab() {
		var curtab = parseInt($('#curtab').val());
		var prevtab = curtab - 1;
		var tabname = formControl(prevtab);
		if (tabname.length > 1) {
			showForm(tabname);
		} else {
			return;
		}
	}

	function updateMSC(productcode) {
		var xsel = $('#apl_msc').val();
		// var pinjaman = $('#ben_pinjamincome').val() * 1;
		var pinjaman = parseInt($('#ben_pinjamincome').val().replace(/,/g, ""), 10) * 1;
		if (xsel != '') {
			var url = "<?php echo site_url(); ?>ajax/tsr/get_mscdetail/" + xsel + "/";

			$.ajax({
				url: url,
				type: "GET",
				success: function(resp) {
					if (resp != 'ERR') {
						var prx = $.parseJSON(resp);
						$('#tenor_option').empty();
						$.each(prx, function(i, l) {
							var angsuran = simulasiPinjaman(pinjaman, l.bunga, l.tenor);
							$('#tenor_option').append('<p><input type="radio" id="ben_limiteditionopt' + i + '" name="ben_limiteditionopt" value="' + l.tenor + '" data-bunga="' + l.bunga + '" data-productcode="' + l.product_code + '" onclick="parseBunga(this)" /> ' + l.description + ' - <span id="angsuran_' + i + '" class="angsuranread" >' + angsuran + '</span></p>');
							$('#angsuran_' + i).priceFormat({
								prefix: 'Rp.',
								thousandsSeparator: ',',
								centsLimit: 0
							});
							$('input[data-productcode="' + productcode + '"]').attr('checked', 'TRUE');
							$('#ben_pinjamread').html( $('#ben_pinjamincome').val());
							// $('#ben_pinjamread').priceFormat({
							// 	prefix: 'Rp.',
							// 	thousandsSeparator: ',',
							// 	centsLimit: 0
							// });
							//hitungCicilan();
						});
					} else {
						$('#tenor_option').empty();
						$('#tenor_option').append('-Choose MSC-');
					}
				}
			});
		}
	}

	$('#pku_datepickerclear').click(function() {
		$('#pku_date').val('');
		$('#zone_id').val('');
	});

	$('#pku_datepicker').click(function() {
		start_zoneMapper();
	});

	function start_zoneMapper() {
		var kecamatan = $('#pku_kecamatan').val();
		var zipcode = $('#pku_zipcode').val();

		$.ajax({
			url: "<?php echo site_url(); ?>autocomplete/find/zone_mapper",
			type: "POST",
			data: {
				kecamatan: kecamatan,
				zipcode: zipcode
			},
			success: function(response) {
				var json = $.parseJSON(response);
				$('#zone_id').val(json.zone);
				start_datePlanner(json.zone);
			}
		});
	}

	function start_datePlanner(zone) {
		//$('#tb_main1').remove();
		if (!zone || zone == '' || zone == 'undefined') {
			zone = 'NOZONE';
			var img = "<img src='<?= base_url() ?>component/images/warning.png' alt='warning' width='48' />";
			var prom = new Boxy('<table style="width:500px;"><tr><td style="min-width:100px">' + img + '</td><td>Area ini tidak termasuk dalam daftar area yang bisa dipickup oleh kurir, <br/>Mohon diarahkan untuk dipickup ditempat lain.</td></tr></table>', {
				modal: true,
				title: "Notice:"
			});
			return;
		}
		var url = "<?php echo site_url(); ?>ajax/tsr/dateplanner_ui/" + zone;
		var myWindow = window.open(url, '-Date Planner-', 'width=750,height=350');
	}

	$(document).ready(function() {
		$(window).bind('beforeunload', function() {
			return 'Form aplikasi masih terbuka, yakin untuk meninggalkan halaman ini ?';
		});
	});

	$('#bio_socialexp_lifetime').click(function() {
		var flag = $(this).is(':checked');
		if (flag == true) {
			//seumur hidup
			$('#bio_socialexp').val('2030-12-31');
			$('#bio_socialexp').attr('readonly', 'true');
			$('#bio_socialexp').next().hide();
		} else {
			//tidak seumur hidup
			$('#bio_socialexp').val('YYYY-MM-DD');
			$('#bio_socialexp').removeAttr('readonly');
			$('#bio_socialexp').next().show();
		}
	});

	$('#bio_npwp_opt').click(function() {
		var flag = $(this).is(':checked');
		if (flag == true) {
			// tidak ada npwp
			$('#bio_npwp').attr('readonly', 'true');
		} else {
			// ada npwp
			$('#bio_npwp').removeAttr('readonly');
		}
	});

	function hanyaAngka(evt) {
		var charCode = (evt.which) ? evt.which : event.keyCode
		if (charCode > 31 && (charCode < 48 || charCode > 57))

			return false;
		return true;
	}

	function resetPaytype() {
		$('#ang_paytype1').removeAttr('checked');
		$('#ang_paytype2').removeAttr('checked');
	}

	$('input[name="pjm_bankopt"]').click(function() {
		var xobj = $('input[name="pjm_bankopt"]');
		$.each(xobj, function(i, l) {
			if ($(l).is(':checked')) {
				var camptype = "<?php echo $prospect_detail['campaign_type']; ?>";
				var val = $(l).val();
				if (val == 'UOB') {
					$('#pjm_accnopermata').val($('#ang_rekening').val()).removeAttr('readonly');
					$('#pjm_accnoother').val('').attr('readonly', 'TRUE');
					$('#pjm_banknameother').val('').attr('readonly', 'TRUE').attr('disabled', 'TRUE');
					if (camptype == '2' || camptype == '3') {
						$('#pjm_accnopermata').attr('readonly', 'TRUE');
					}
				} else if (val == 'OTHER_BANK') {
					var obank = $('#pjm_banknameother_hidden').val();
					$('#pjm_accnopermata').val('').attr('readonly', 'TRUE');
					$('#pjm_accnoother').val($('#pjm_accnoother_hidden').val()).removeAttr('readonly');
					//$('#pjm_banknameother').val($('#pjm_banknameother_hidden').val()).removeAttr('readonly').removeAttr('disabled');
					$('#pjm_banknameother option[data-bcode="' + obank + '"]').attr('selected', 'true');
					$('#pjm_banknameother').removeAttr('readonly').removeAttr('disabled');
				}
			}
		});
		//check_bankoption();
	});
</script>

<form id="frmAplikasi" action="<?php echo $submit1_url; ?>" method="post" autocomplete="off">
	<div style="width:800px; padding:10px">
		<div class="formTab" id="apl" onclick="showForm(this.id);">Aplikasi</div>
		<div class="formTab" id="ben" onclick="showForm(this.id);">Fas Pinjaman</div>
		<div class="formTab" id="pjm" onclick="showForm(this.id);">Data Pinjaman</div>
	</div>

	<!-- Form Pilihan Aplikasi -->

	<div id="divAplikasi" class="formContainer" style="display:block">

		<table class="appform">
			<tr>
				<th colspan="3">Form Aplikasi</th>
			</tr>
			<tr class="b">
				<td><label>MSC</label></td>
				<td>:</td>
				<td>
					<select name="apl_msc" id="apl_msc" style="width:300px; border-color:red">
						<option value="">- MSC Code -</option>
						<?php foreach ($productcode as $row) { ?>
							<option value="<?php echo $row['msc']; ?>"><?php echo $row['msc']; ?> - <?php echo $row['segment']; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>

			<tr class="a">
				<td><label>Nocase / Apps ID</label></td>
				<td>:</td>
				<td><input type="text" id="apl_appid" name="apl_appid" value="<?php echo @$prospect_detail['id_prospect']; ?>" readonly /></td>
			</tr>

			<tr class="b">
				<td><label>Sales</label></td>
				<td>:</td>
				<td><input style="text-transform:uppercase" type="text" id="apl_sellercode" name="apl_sellercode" value="<?php echo @$_SESSION['username']; ?>" readonly /></td>
			</tr>
			<!--	
				<tr class="a">
					<td><label>Seq No</label></td>
					<td>:</td>
					<td><input type="text" id="apl_seqno" name="apl_seqno" value="" /></td>
				</tr>
-->
			<tr class="a hide">
				<td><label>Gift Code</label></td>
				<td>:</td>
				<td><input type="text" class="hide" id="apl_giftcode" name="apl_giftcode" value="<?php echo @$prospect_detail['gift_code']; ?>" readonly /></td>
			</tr>

			<tr class="b">
				<td><label>Product Code</label></td>
				<td>:</td>
				<td><input type="text" id="apl_productcode" name="apl_productcode" value="" readonly /></td>
			</tr>
			
			<tr class="a">
				<td><label>Agree Notes</label></td>
				<td>:</td>
				<td><textarea id="agree_notes" name="agree_notes" maxlength="300"></textarea></td>
			</tr>
		</table>

	</div>

	<!-- Form Bio Data -->

	<div id="frmBioData" class="formContainer" style="display:none">
		<table class="appform">
			<tr>
				<th colspan="3">Bio Data</th>
			</tr>
			<tr class="a">
				<td><label>Nama Lengkap Sesuai KTP </label></td>
				<td>:</td>
				<td><input type="text" id="bio_socialname" name="bio_socialname" value="<?php echo strtoupper(@$prospect_detail['fullname']); ?>" <?php if ($prospect_detail['campaign_type'] == '3') {
																																						echo 'readonly';
																																					} ?> /></td>
			</tr>

			<tr class="b">
				<td><label>Nama Lengkap Tanpa Singkatan <span class="smallInfo">(tanpa gelar tanpa singkatan) </span></label></td>
				<td>:</td>
				<td><input type="text" id="bio_fullname" name="bio_fullname" value="<?php echo strtoupper(@$prospect_detail['fullname']); ?>" <?php if ($prospect_detail['campaign_type'] == '3') {
																																					echo 'readonly';
																																				} ?> /></td>
			</tr>

			<tr class="a">
				<td><label>Nama Inisial/Alias</label></td>
				<td>:</td>
				<td><input type="text" id="bio_inisial" name="bio_inisial" value="" /></td>
			</tr>

			<!-- Umrah -->
			<!-- BUG: Disable				<tr class="a">
					<td><label>Gelar sebelum nama <span class="cmpInfo">(* hanya untuk formulir umrah) </span></label></td>
					<td>:</td>
					<td><input type="text" id="bio_titlebefore" name="bio_titlebefore" value="" maxlength="24" readonly /></td>
				</tr> -->

			<!-- Umrah -->
			<!-- BUG: Disable				<tr class="b">
					<td><label>Gelar sesudah nama <span class="cmpInfo">(* hanya untuk formulir umrah) </span></label></td>
					<td>:</td>
					<td><input type="text" id="bio_titleafter" name="bio_titleafter" value="" maxlength="24" readonly /></td> 
				</tr> -->

			<tr class="b">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>

			<tr class="a">
				<td><label>No KTP</label></td>
				<td>:</td>
				<td><input type="text" id="bio_socialcode" name="bio_socialcode" value="<?php echo @$prospect_detail['social_number']; ?>" <?php if ($prospect_detail['campaign_type'] == '3') {
																																				echo 'readonly';
																																			} ?> /> Expired <?php echo show_calender('bio_socialexp', 'YYYY-MM-DD', 'bio_socialexp'); ?> <input type="checkbox" id="bio_socialexp_lifetime" name="bio_socialexp_lifetime" /> Seumur Hidup</td>
			</tr>

			<tr class="b">
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<!--
				<tr class="b" style="display:none">
					<td><label>Tempat Lahir & Tanggal Lahir(YYYY-MM-DD) </label></td>
					<td>:</td>
					<td><input type="text" id="bio_bop" name="bio_bop" value="" <?php if ($prospect_detail['campaign_type'] == '2' || $prospect_detail['campaign_type'] == '3') ?> /> &nbsp - &nbsp
						<input type="text" id="bio_dob" name="bio_dob" value="" <?php if ($prospect_detail['campaign_type'] == '2' || $prospect_detail['campaign_type'] == '3') {
																					echo 'readonly';
																				} ?> />
					</td>
				</tr>
				-->
			<tr class="b">
				<td><label>Tempat Lahir &amp; Tanggal Lahir(YYYY-MM-DD) </label></td>
				<td>:</td>
				<td><input type="text" id="bio_bop" name="bio_bop" value="<?php echo @$prospect_detail['pob']; ?>" <?php if ($prospect_detail['campaign_type'] == '3') {
																														echo 'readonly';
																													} ?> />
					<input type="text" id="bio_dob" name="bio_dob" value="YYYY-MM-DD" readonly="true" />
				</td>
			</tr>
			<tr class="a">
				<td><label>Jenis Kelamin</label></td>
				<td>:</td>
				<td>
					<input type="radio" name="bio_genderopt" id="bio_genderopt1" value="M" <?php if ($prospect_detail['gender'] == 'M' || $prospect_detail['gender'] == 'L') echo 'CHECKED'; ?> /> Pria &nbsp
					<input type="radio" name="bio_genderopt" id="bio_genderopt2" value="F" <?php if ($prospect_detail['gender'] == 'F' || $prospect_detail['gender'] == 'P') echo 'CHECKED'; ?> /> Wanita &nbsp
				</td>
			</tr>

			<tr class="b">
				<td><label>CIF</label></td>
				<td>:</td>
				<td>
					<input type="text" id="bio_cif" name="bio_cif" value="<?php echo @$prospect_detail['cnum']; ?>" readonly />
				</td>
			</tr>

			<tr class="a">
				<td><label>Alamat Sesuai KTP</label></td>
				<td>:</td>
				<td>
					<textarea id="bio_sociaddr" name="bio_sociaddr" autocomplete="off" maxlength="200"><?php echo TRIM(@$prospect_detail['home_address1']); ?></textarea>
				</td>
			</tr>

			<tr class="b">
				<td><label>Kode Pos<span class="smallInfo">(Wajib diisi)</span></label></td>
				<td>:</td>
				<td><input type="text" id="bio_zipcode" name="bio_zipcode" value="" maxlength="5" list="zipcode" autocomplete="off" />
					<datalist id="zipcode"></datalist>
				</td>
			</tr>

			<tr class="a">
				<td><label>Kelurahan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="bio_kelurahan" name="bio_kelurahan" value="" list="listkel" autocomplete="off" />
					<datalist id="listkel">Test</datalist>
				</td>
			</tr>

			<tr class="b">
				<td><label>Kecamatan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="bio_kecamatan" name="bio_kecamatan" value="" list="listkec" autocomplete="off" />
					<datalist id="listkec"></datalist>
				</td>
			</tr>

			<tr class="a" style="display:none">
				<td><label>Kotamadya / Kabupaten</label></td>
				<td>:</td>
				<td><input type="text" id="bio_kabupaten" name="bio_kabupaten" value="" autocomplete="off" /></td>
			</tr>

			<tr class="b">
				<td><label>Kota</label></td>
				<td>:</td>
				<td><input type="text" id="bio_city" name="bio_city" list="cities" autocomplete="off" />
					<datalist id="cities"></datalist>
				</td>
			</tr>

			<tr class="a">
				<td><label>RT / RW</label></td>
				<td>:</td>
				<td><input type="text" id="bio_rt" name="bio_rt" value="" class="smallInput" maxlength="5" /> / <input type="text" id="bio_rw" name="bio_rw" value="" class="smallInput" maxlength="5" /></td>
			</tr>

			<tr class="b">
				<td><label>Sama dengan alamat KTP</label></td>
				<td>:</td>
				<td>
					<input type="checkbox" id="sameas_social" name="sameas_social" />
				</td>
			</tr>

			<tr class="a">
				<td><label>Alamat tempat tinggal saat ini</label></td>
				<td>:</td>
				<td><textarea id="bio_addr" name="bio_addr" maxlength="200"></textarea></td>
			</tr>

			<tr class="b">
				<td><label>RT / RW</label></td>
				<td>:</td>
				<td><input type="text" id="bio_billrt" name="bio_billrt" value="" class="smallInput" maxlength="5" /> / <input type="text" id="bio_billrw" name="bio_billrw" value="" class="smallInput" maxlength="5" /></td>
			</tr>

			<tr class="a">
				<td><label>Kode Pos<span class="smallInfo">(Wajib diisi)</span></label></td>
				<td>:</td>
				<td><input type="text" id="bio_billzipcode" name="bio_billzipcode" value="" maxlength="5" /></td>
			</tr>

			<tr class="b">
				<td><label>Kelurahan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="bio_billkelurahan" name="bio_billkelurahan" value="" list="kelurahan2" autocomplete="off" />
					<datalist id="kelurahan2"></datalist>
				</td>
			</tr>

			<tr class="a">
				<td><label>Kecamatan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="bio_billkecamatan" name="bio_billkecamatan" value="" list="kecamatan2" />
					<datalist id="kecamatan2"></datalist>
				</td>
			</tr>

			<tr class="b" style="display:none">
				<td><label>Kotamadya / Kabupaten</label></td>
				<td>:</td>
				<td><input type="text" id="bio_billkabupaten" name="bio_billkabupaten" value="" /></td>
			</tr>

			<tr class="a">
				<td><label>Kota</label></td>
				<td>:</td>
				<td>
					<input type="text" id="bio_billcity" name="bio_billcity" value="" list="kota2" autocomplete="off" />
					<datalist id="kota2"></datalist>
				</td>
			</tr>

			<tr class="b">
				<td><label>Pendidikan terakhir</label></td>
				<td>:</td>
				<td>
					<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt1" value="SMA" /> SMA/SMU &nbsp;
					<!-- BUG: 						
                        <input type="radio" name="bio_lasteduopt" id="bio_lasteduopt2" value="AKADEMI" > Akademi &nbsp
						<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt3" value="UNIVERSITAS"> Universitas (S1 / S2 / S3) &nbsp<br/> 
-->
					<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt5" value="D1" /> D1 &nbsp;
					<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt6" value="D2" /> D2 &nbsp;
					<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt7" value="D3" /> D3 &nbsp;
					<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt8" value="S1" /> S1 &nbsp;
					<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt9" value="S2" /> S2 &nbsp;
					<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt10" value="S3" /> S3 &nbsp;
					<input type="radio" name="bio_lasteduopt" id="bio_lasteduopt4" value="OTHER" /> Lainnya &nbsp;
					<input type="text" name="bio_lastedu_other" id="bio_lastedu_other" value="" style="display:none" />
				</td>
			</tr>

			<tr class="a">
				<td><label>Lama Menempati</label></td>
				<td>:</td>
				<td><input type="number" id="bio_billresideyear" name="bio_billresideyear" value="" class="smallInput" maxlength="5" min="0" />&nbsp Tahun&nbsp&nbsp<input type="number" id="bio_billresidemonth" name="bio_billresidemonth" value="" class="smallInput" maxlength="5" min="0" max="12" />&nbsp Bulan</td>
			</tr>

			<tr class="b">
				<td><label>Telp Rumah</label></td>
				<td>:</td>
				<td>
					<input type="text" id="bio_billhomephonearea" name="bio_billhomephonearea" value="" maxlength="4" class="smallInput" />
					-
					<input type="text" id="bio_billhomephone" name="bio_billhomephone" value="<?php echo @$prospect_detail['home_phone1']; ?>" />
				</td>
			</tr>

			<tr class="a">
				<td><label>Status tempat tinggal sekarang</label></td>
				<td>:</td>
				<td>
					<input type="radio" name="billhome_opt" value="SELF" /> Milik Sendiri &nbsp;
					<input type="radio" name="billhome_opt" value="RENT" /> Sewa &nbsp;
					<input type="radio" name="billhome_opt" value="FAMILY" /> Milik Keluarga &nbsp;
					<input type="radio" name="billhome_opt" value="BOARD" /> Kost &nbsp;
					<input type="radio" name="billhome_opt" value="CORP" /> Milik Perusahaan &nbsp;
					<input type="radio" name="billhome_opt" value="OTHER" /> Lainnya &nbsp;
				</td>
			</tr>

			<tr class="b">
				<td><label>No. Ponsel</label></td>
				<td>:</td>
				<td>
					<input type="text" id="bio_billcellular" name="bio_billcellular" value="" />
					<!-- <a style="cursor:pointer" onclick="get_mapnumber('map_hp1');">Sellular 1</a>&nbsp;|&nbsp;<a style="cursor:pointer" onclick="get_mapnumber('map_hp2')">Sellular 2</a> -->
				</td>
			</tr>

			<tr class="a">
				<td><label>NPWP</label>&nbsp;</td>
				<td>:</td>
				<td><input type="text" id="bio_npwp" name="bio_npwp" value="" />&nbsp;|&nbsp;<input type="checkbox" id="bio_npwp_opt" name="bio_npwp_opt" /> Tidak Memiliki NPWP</td>
			</tr>

			<tr class="b">
				<td><label>Email</label></td>
				<td>:</td>
				<td><input type="email" id="bio_email" name="bio_email" value="" /></td>
			</tr>

			<tr class="a">
				<td><label>Agama</label></td>
				<td>:</td>
				<td>
					<select id="bio_religion" name="bio_religion">
						<option value="">- Agama -</option>
						<option value="ISLAM">Islam</option>
						<option value="KRISTEN">Kristen Protestan</option>
						<option value="KATOLIK">Katolik</option>
						<option value="HINDU">Hindu</option>
						<option value="BUDDHA">Buddha</option>
						<option value="KONGHUCU">Kong Hu Cu</option>
					</select>
				</td>
			</tr>

			<tr class="b">
				<td><label>Nama Ibu Kandung sebelum menikah</label>&nbsp; tanpa singkatan</td>
				<td>:</td>
				<td><input type="text" id="bio_maidenname" name="bio_maidenname" value="" /></td>
			</tr>

			<tr class="a">
				<td><label>Nama Suami/Istri</label>&nbsp;</td>
				<td>:</td>
				<td><input type="text" id="bio_spouse" name="bio_spouse" value="" /></td>
			</tr>

			<tr class="b">
				<td><label>Status Pernikahan</label></td>
				<td>:</td>
				<td>
					<input type="radio" name="bio_maritalopt" id="bio_maritalopt1" value="M" /> Lajang &nbsp
					<input type="radio" name="bio_maritalopt" id="bio_maritalopt2" value="S" /> Menikah &nbsp
					<input type="radio" name="bio_maritalopt" id="bio_maritalopt3" value="D" /> Cerai &nbsp
				</td>
			</tr>

			<tr class="a">
				<td><label>Jumlah Tanggungan</label></td>
				<td>:</td>
				<td><input type="number" id="bio_dependent" name="bio_dependent" value="" class="smallInput" min="0" /> Orang</td>
			</tr>

			<tr class="b">
				<td><label>Alamat Pengiriman Kartu dan Lembar Tagihan</label></td>
				<td>:</td>
				<td>
					<input type="radio" name="bio_mailingopt" id="bio_mailingopt1" value="RUMAH" /> Rumah &nbsp
					<input type="radio" name="bio_mailingopt" id="bio_mailingopt2" value="KANTOR" /> Kantor &nbsp
				</td>
			</tr>

		</table>
	</div>

	<!-- Form Data Pekerjaan -->

	<div id="frmPekerjaan" class="formContainer" style="display:none">
		<table class="appform">
			<tr>
				<th colspan="3">Data Pekerjaan</th>
			</tr>
			<tr class="a">
				<td><label>Pekerjaan</label></td>
				<td>:</td>
				<td>
					<p><input type="radio" name="ocp_occupationopt" value="PEGAWAISWASTA" /> Pegawai Swasta</p>
					<p><input type="radio" name="ocp_occupationopt" value="WIRASWASTA" /> Wiraswasta</p>
					<p><input type="radio" name="ocp_occupationopt" value="PROFESIONAL" /> Profesional</p>
					<p><input type="radio" name="ocp_occupationopt" value="PEGAWAINEGERI" /> Pegawai Negeri</p>
					<p><input type="radio" name="ocp_occupationopt" value="OTHER" /> Other</p>
				</td>
			</tr>

			<tr class="b">
				<td><label>Jenis Perusahaan</label></td>
				<td>:</td>
				<td>
					<p><input type="radio" name="ocp_occutypeopt" value="MNC/Tbk" /> MNC / Tbk</p>
					<p><input type="radio" name="ocp_occutypeopt" value="PT_SME/ME" /> PT (SME / ME)</p>
					<p><input type="radio" name="ocp_occutypeopt" value="PT_nonTbk" /> PT (BUMN Non Tbk)</p>
					<p><input type="radio" name="ocp_occutypeopt" value="PEMERINTAHAN"> Pemerintahan</p>
					<p><input type="radio" name="ocp_occutypeopt" value="CV" /> CV</p>
					<p><input type="radio" name="ocp_occutypeopt" value="PD" /> PD</p>
					<p><input type="radio" name="ocp_occutypeopt" value="PERORANGAN" /> Perorangan</p>
					<p><input type="radio" name="ocp_occutypeopt" value="YAYASAN" /> Yayasan</p>
				</td>
			</tr>

			<tr class="a">
				<td><label>Nama Perusahaan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="ocp_companyname" name="ocp_companyname" style="width:400px" value="<?php echo $prospect_detail['company_name']; ?>" <?php if ($prospect_detail['campaign_type'] == '2') {
																																									echo 'readonly';
																																								} ?> />
				</td>
			</tr>

			<tr class="b">
				<td><label>Bidang Usaha</label></td>
				<td>:</td>
				<td>
					<input type="text" id="ocp_businessline" name="ocp_businessline" value="<?php echo $prospect_detail['bidang_usaha']; ?>" list="bidangusaha_keyword" style="width:200px;" />
					<datalist id="bidangusaha_keyword"></datalist>
				</td>
			</tr>

			<tr class="a">
				<td><label>Lama Bekerja</label></td>
				<td>:</td>
				<td><input type="number" id="ocp_yearduration" name="ocp_yearduration" value="" class="smallInput" maxlength="5" min="0" /> &nbsp Tahun &nbsp <input type="number" id="ocp_monthduration" name="ocp_monthduration" value="" class="smallInput" min="0" /> &nbsp Bulan</td>
			</tr>

			<tr class="b">
				<td><label>Lama Bekerja di Perusahaan Sebelumnya</label></td>
				<td>:</td>
				<td><input type="number" id="ocp_lastyearduration" name="ocp_lastyearduration" value="" class="smallInput" min="0" /> &nbsp Tahun &nbsp <input type="number" id="ocp_lastmonthduration" name="ocp_lastmonthduration" value="" class="smallInput" min="0" /> &nbsp Bulan</td>
			</tr>

			<tr class="a">
				<td><label>Jumlah Karyawan<span class="smallInfo">(bagi pengusaha swasta)</span></label></td>
				<td>:</td>
				<td>
					<select id="ocp_empcount" name="ocp_empcount">
						<option value="">-Pilih-</option>
						<option value="10-"> 10- </option>
						<option value="10-25"> 10-25 </option>
						<option value="50-100"> 50-100 </option>
						<option value="100+"> 100+ </option>
					</select>
				</td>
			</tr>

			<tr class="b">
				<td><label>Status Pekerjaan</label></td>
				<td>:</td>
				<td>
					<input type="radio" name="ocp_occustatopt" value="TETAP"> Tetap &nbsp
					<input type="radio" name="ocp_occustatopt" value="KONTRAK"> Kontrak &nbsp
					<input type="radio" name="ocp_occustatopt" value="FREELANCE / PARTIME"> Partime / Freelance &nbsp
					<input type="radio" name="ocp_occustatopt" value=""> Lainnya &nbsp
				</td>
			</tr>

			<tr class="a">
				<td><label>Jabatan</label></td>
				<td>:</td>
				<td>
					<select id="ocp_position" name="ocp_position">
						<option value="">-Pilih-</option>
						<option value="STAFF">STAFF</option>
						<option value="SUPERVISOR">SUPERVISOR</option>
						<option value="MANAGER">MANAGER</option>
						<option value="DIREKTUR">DIREKTUR</option>
						<option value="PEMILIK">PEMILIK</option>
					</select>
				</td>
			</tr>

			<tr class="b">
				<td><label>Bagian</label></td>
				<td>:</td>
				<td><input type="text" id="ocp_division" name="ocp_division" value="" /></td>
			</tr>

			<tr class="a">
				<td><label>Alamat kantor / Tempat usaha</label></td>
				<td>:</td>
				<td>
					<textarea id="ocp_officeaddr" name="ocp_officeaddr" value="" maxlength="200"></textarea>
				</td>
			</tr>

			<tr class="b">
				<td><label>Kode Pos<span class="smallInfo">(Wajib diisi)</span></label></td>
				<td>:</td>
				<td><input type="text" id="ocp_zipcode" name="ocp_zipcode" value="" maxlength="5" /></td>
			</tr>

			<tr class="a">
				<td><label>Kelurahan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="ocp_kelurahan" name="ocp_kelurahan" value="" list="kelurahan3" />
					<datalist id="kelurahan3"></datalist>
				</td>
			</tr>

			<tr class="b">
				<td><label>Kecamatan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="ocp_kecamatan" name="ocp_kecamatan" value="" list="kecamatan3" />
					<datalist id="kecamatan3"></datalist>
				</td>
			</tr>

			<tr class="a" style="display:none">
				<td><label>Kabupaten</label></td>
				<td>:</td>
				<td><input type="text" id="ocp_kabupaten" name="ocp_kabupaten" value="" /></td>
			</tr>

			<tr class="b">
				<td><label>Kota</label></td>
				<td>:</td>
				<td>
					<input type="text" id="ocp_city" name="ocp_city" value="" list="kota3" />
					<datalist id="kota3"></datalist>
				</td>
			</tr>

			<tr class="a">
				<td><label>Telp Kantor</label></td>
				<td>:</td>
				<td><input type="text" id="ocp_officephonearea" name="ocp_officephonearea" value="" class="smallInput" maxlength="4" /> - <input type="text" id="ocp_officephone" name="ocp_officephone" value="" /> Ext <input type="text" id="ocp_officeext" name="ocp_officeext" value="" class="smallInput" /></td>
			</tr>

			<tr class="b">
				<td><label>No Fax</label></td>
				<td>:</td>
				<td><input type="text" id="ocp_fax" name="ocp_fax" value="" /></td>
			</tr>

			<tr class="a">
				<td><label>Penghasilan Kotor per Bulan</label></td>
				<td>:</td>
				<td>Rp.&nbsp<input type="text" id="ocp_grossincome" name="ocp_grossincome" value="" onblur="formatThis(this)" /></td>
			</tr>

			<tr class="b">
				<td><label>Penghasilan Tambahan per Bulan<span class="smallinfo">(jika ada)</span></label></td>
				<td>:</td>
				<td>Rp.&nbsp<input type="text" id="ocp_additionalincome" name="ocp_additionalincome" value="" onblur="formatThis(this)" /></td>
			</tr>

			<tr class="a">
				<td><label>Sumber Pendapatan Tambahan</label></td>
				<td>:</td>
				<td><input type="text" id="ocp_additionalsource" name="ocp_additionalsource" value="" /></td>
			</tr>

			<tr class="b">
				<td><label><strong>Diisi untuk Wirausaha / Profesional</strong></label></td>
				<td>&nbsp</td>
				<td>&nbsp</td>
			</tr>

			<tr class="a">
				<td><label>No. SIUP</label></td>
				<td>:</td>
				<td><input type="text" id="ocp_nosiup" name="ocp_nosiup" value="" /></td>
			</tr>

			<tr class="b">
				<td><label>Omset / bulan</label></td>
				<td>:</td>
				<td>Rp.&nbsp<input type="text" id="ocp_omset" name="ocp_omset" value="" onblur="formatThis(this)" /></td>
			</tr>

			<tr class="a">
				<td><label>% Kepemilikan</label></td>
				<td>:</td>
				<td><input type="number" id="ocp_persen" name="ocp_persen" value="" class="smallInput" maxlength="5" min="0" max="100" /></td>
			</tr>

			<tr class="b">
				<td><label>Tanggal perusahaan berdiri<span class="smallinfo">(tgl / bln / thn)</span></label></td>
				<td>:</td>
				<td><input type="text" id="ocp_tgl" name="ocp_tgl" value="" class="smallInput" maxlength="5" /> / <input type="text" id="ocp_bln" name="ocp_bln" value="" class="smallInput" maxlength="5" /> / <input type="text" id="ocp_thn" name="ocp_thn" value="" class="smallInput" maxlength="5" /></td>
			</tr>

		</table>
	</div>

	<!-- Form Data Keluarga -->

	<div id="frmKeluarga" class="formContainer" style="display:none">
		<table class="appform">
			<tr>
				<th colspan="3">Data Keluarga Dekat Yang Tidak Tinggal Serumah</th>
			</tr>

			<tr class="a">
				<td><label>Nama Lengkap</label></td>
				<td>:</td>
				<td><input type="text" id="fam_fullname" name="fam_fullname" value="" /></td>
			</tr>

			<tr class="b">
				<td>Alamat</td>
				<td>:</td>
				<td><textarea id="fam_addr" name="fam_addr" maxlength="200"></textarea></td>
			</tr>

			<tr class="a">
				<td><label>Kode Pos</label></td>
				<td>:</td>
				<td><input type="text" id="fam_zipcode" name="fam_zipcode" value="" maxlength="5" /></td>
			</tr>

			<tr class="b">
				<td><label>Kelurahan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="fam_kelurahan" name="fam_kelurahan" value="" list="kelurahan4" />
					<datalist id="kelurahan4"></datalist>
				</td>
			</tr>

			<tr class="a">
				<td><label>Kecamatan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="fam_kecamatan" name="fam_kecamatan" value="" list="kecamatan4" />
					<datalist id="kecamatan4"></datalist>
				</td>
			</tr>

			<tr class="b" style="display: none;">
				<td><label>Kabupaten</label></td>
				<td>:</td>
				<td><input type="text" id="fam_kabupaten" name="fam_kabupaten" value="" /></td>
			</tr>

			<tr class="a">
				<td><label>Kota</label></td>
				<td>:</td>
				<td>
					<input type="text" id="fam_city" name="fam_city" value="" list="kota4" />
					<datalist id="kota4"></datalist>
				</td>
			</tr>

			<tr class="b">
				<td><label>RT / RW</label></td>
				<td>:</td>
				<td><input type="text" id="fam_rt" name="fam_rt" value="" class="smallInput" maxlength="5" /> / <input type="text" id="fam_rw" name="fam_rw" value="" class="smallInput" maxlength="5" /></td>
			</tr>

			<tr class="a">
				<td><label>Telp Rumah</label></td>
				<td>:</td>
				<td>
					<input type="text" id="fam_homephonearea" name="fam_homephonearea" value="" maxlength="4" style="width:50px" />
					-
					<input type="text" id="fam_homephone" name="fam_homephone" value="" />
				</td>
			</tr>

			<tr class="b">
				<td><label>No. Ponsel</label></td>
				<td>:</td>
				<td><input type="text" id="fam_cellular" name="fam_cellular" value="" /></td>
			</tr>

			<!-- Umrah dan Multiguna-->
			<tr class="a">
				<td><label>Telp Kantor</label></td>
				<td>:</td>
				<td>
					<input type="text" id="fam_officephonearea" name="fam_officephonearea" value="" maxlength="4" style="width:50px" />
					-
					<input type="text" id="fam_officephone" name="fam_officephone" value="" />
				</td>
			</tr>

			<tr class="b">
				<td><label>Status tempat tinggal sekarang</label></td>
				<td>:</td>
				<td>
					<input type="radio" name="famhome_opt" value="SELF" /> Milik Sendiri &nbsp
					<input type="radio" name="famhome_opt" value="RENT" /> Sewa &nbsp
					<input type="radio" name="famhome_opt" value="FAMILY" /> Milik Keluarga &nbsp
					<input type="radio" name="famhome_opt" value="BOARD" /> Kost &nbsp
					<input type="radio" name="famhome_opt" value="CORP" /> Milik Perusahaan &nbsp
					<input type="radio" name="famhome_opt" value="OTHER" /> Lainnya &nbsp
				</td>
			</tr>

			<tr class="a">
				<td><label>Lama Menempati</label></td>
				<td>:</td>
				<td><input type="number" id="fam_billresideyear" name="fam_billresideyear" value="" class="smallInput" maxlength="5" min="0" />&nbsp Tahun&nbsp&nbsp<input type="number" id="fam_billresidemonth" name="fam_billresidemonth" value="" class="smallInput" maxlength="5" min="0" max="12" />&nbsp Bulan</td>
			</tr>

			<tr class="b">
				<td><label>Hubungan Keluarga</label></td>
				<td>:</td>
				<td>
					<p><input type="radio" name="fam_relationopt" value="ORTU" /> <label>Orang Tua</label></p>
					<p><input type="radio" name="fam_relationopt" value="KAKAK/ADIK" /> <label>Kakak/Adik</label></p>
					<p><input type="radio" name="fam_relationopt" value="KAKAK/ADIK-IPAR" /> <label>Kakak/Adik Ipar</label></p>
					<p><input type="radio" name="fam_relationopt" value="ANAK" /> <label>Anak</label></p>
					<p><input type="radio" name="fam_relationopt" value="SAUDARA" /> <label>Saudara Kandung Ortu</label></p>
					<p><input type="radio" name="fam_relationopt" value="PAMAN/BIBI" /> <label>Paman/Bibi</label></p>
					<p><input type="radio" name="fam_relationopt" value="KAKEK/NENEK" /> <label>Kakek/Nenek</label></p>
				</td>
			</tr>

		</table>
	</div>

	<!-- Form Fasilitas Pinjaman -->

	<div id="frmBenefit" class="formContainer" style="display:none">
		<table class="appform">
			<tr>
				<th colspan="3">Fas Pinjaman</th>
			</tr>

			<tr class="b hide">
				<td><label>Tujuan Pinjaman</label></td>
				<td>:</td>
				<td>
					<p>Barang :</p>
					<p id="ben_pinjamlbl11"><input type="radio" id="ben_pinjamopt11" name="ben_pinjamopt" value="BARANG" onclick="parseBunga3()" /> Pembelian Barang </p>
					<p id="ben_pinjamlbl1"><input type="radio" id="ben_pinjamopt1" name="ben_pinjamopt" value="RENOVASIRUMAH" /> Renovasi Rumah </p>
					<p id="ben_pinjamlbl4"><input type="radio" id="ben_pinjamopt4" name="ben_pinjamopt" value="PERABOTANRUMAH" /> Perabotan Rumah </p>
					<p id="ben_pinjamlbl5"><input type="radio" id="ben_pinjamopt5" name="ben_pinjamopt" value="LIBURAN" /> Liburan </p>
					<p id="ben_pinjamlbl6"><input type="radio" id="ben_pinjamopt6" name="ben_pinjamopt" value="LAINNYA" /> Lainnya &nbsp <input type="text" id="ben_other" name="ben_other" value="" style="display:none" /></p>

					<p>Jasa :</p>
					<p id="ben_pinjamlbl2"><input type="radio" id="ben_pinjamopt2" name="ben_pinjamopt" value="PENDIDIKAN" onclick="parseBunga3()" /> Pendidikan </p> <!-- multiguna jasa -->
					<p id="ben_pinjamlbl3"><input type="radio" id="ben_pinjamopt3" name="ben_pinjamopt" value="PERNIKAHAN" onclick="parseBunga3()" /> Pernikahan </p> <!-- multiguna jasa -->
					<p id="ben_pinjamlbl9"><input type="radio" id="ben_pinjamopt9" name="ben_pinjamopt" value="TRAVELING" onclick="parseBunga3()" /> Traveling </p>
					<p id="ben_pinjamlbl10"><input type="radio" id="ben_pinjamopt10" name="ben_pinjamopt" value="KESEHATAN" onclick="parseBunga3()" /> Kesehatan </p>
					<p id="ben_pinjamlbl12"><input type="radio" id="ben_pinjamopt12" name="ben_pinjamopt" value="LAINNYAJASA" onclick="parseBunga3()" /> Lainnya &nbsp <input type="text" id="ben_otherjasa" name="ben_otherjasa" value="" style="display:none" /></p>
				</td>
			</tr>

			<tr class="b">
				<td><label>Jumlah pinjaman yang diinginkan</label></td>
				<td>: </td>
				<td>
					<?php if (empty($refreshbag)) { ?>
						<input type="text" id="ben_pinjamincome" name="ben_pinjamincome" value="" placeholder="" step="100000" min="1000000" onchange="hitungCicilan()" onblur="checkPinjaman()" <?= $cop_flexibleamount[0]['value'] != "1" ? 'readonly' : ''; ?> /> <!--- script baru-->
						<!-- <input type="number" id="ben_pinjamincome" name="ben_pinjamincome" value="<?= $prospect_detail['max_loan']; ?>" placeholder="<?php echo $prospect_detail['max_loan']; ?>" step="100000" min="1000000" onchange="hitungCicilan()" onblur="checkPinjaman()" <?= $cop_flexibleamount[0]['value'] != "1" ? 'readonly' : ''; ?> /> -->
						<span style="color:#C58A32;font-weight:bold;">Total Pinjaman</span> : <span id="ben_pinjamread"></span> &nbsp/&nbsp <span style="color:#E131EC;font-weight:bold;">Dana Yang diterima</span> : <span id="ben_totalpenerimaandana" style="color:darkgreen">0</span>
					<?php } else { ?>
						<input type="text" id="ben_pinjamincome" name="ben_pinjamincome" value="" placeholder="" step="100000" min="1000000" onchange="hitungCicilan()" onblur="checkPinjaman()" <?= $cop_flexibleamount[0]['value'] != "1" ? 'readonly' : ''; ?> /> <!--- script baru-->
						<!-- <input type="number" id="ben_pinjamincome" name="ben_pinjamincome" value="<?= $refreshbag['max_loan']; ?>" placeholder="<?php echo $refreshbag['max_loan']; ?>" step="100000" min="1000000" onchange="hitungCicilan()" onblur="checkPinjaman()" <?= $cop_flexibleamount[0]['value'] != "1" ? 'readonly' : ''; ?> /> -->
						<span style="color:#C58A32;font-weight:bold;">Total Pinjaman</span> : <span id="ben_pinjamread"></span> &nbsp/&nbsp <span style="color:#E131EC;font-weight:bold;">Dana Yang diterima</span> : <span id="ben_totalpenerimaandana" style="color:darkgreen">0</span>
					<?php } ?>
				</td>
			</tr>
			<tr class="a">
				<td><label>Batas Pinjaman</label></td>
				<td>:</td>
				<td>
					<?php if (empty($refreshbag)) { ?>
						<span id="maxloan_read"><?php echo 'Rp. ' . price_format($prospect_detail['max_loan']); ?></span>&nbsp;
						<span id="max_loan_terbilang" data-format="terbilang"><?= $prospect_detail['max_loan']; ?></span>
						<input type="hidden" id="pjm_maxloan" name="pjm_maxloan" value="<?= $prospect_detail['max_loan']; ?>" />
					<?php } else { ?>
						<span id="maxloan_read"><?php echo 'Rp. ' . price_format($refreshbag['max_loan']); ?></span>&nbsp;
						<span id="max_loan_terbilang" data-format="terbilang"><?= $refreshbag['max_loan']; ?></span>
						<input type="hidden" id="pjm_maxloan" name="pjm_maxloan" value="<?= $refreshbag['max_loan']; ?>" />
					<?php } ?>
				</td>
			</tr>
			<tr class="a">
				<td><label>Loan 1</label></td>
				<td>:</td>
				<td>
					<?php if (empty($refreshbag)) { ?>
						<input type="text" id="pjm_loan1" name="pjm_loan1" value="<?= $prospect_detail['loan1']; ?>" placeholder="<?php echo $prospect_detail['loan1']; ?>" step="100000" min="1000000" onchange="hitungCicilan()" onblur="checkPinjaman()" <?= $cop_flexibleamount[0]['value'] != "1" ? '' : ''; ?> /> <!--- script baru-->
						<span id="loan1_read"><?php echo 'Rp. ' . price_format($prospect_detail['loan1']); ?></span>&nbsp;
						<span id="loan1_terbilang" data-format="terbilang"><?= $prospect_detail['loan1']; ?></span>
						<!-- <input type="hidden" id="pjm_loan1" name="pjm_loan1" value="<?= $prospect_detail['loan1']; ?>" /> -->
					<?php } else { ?>
						<input type="text" id="pjm_loan1" name="pjm_loan1" value="<?= $refreshbag['loan1']; ?>" placeholder="<?php echo $refreshbag['loan1']; ?>" step="100000" min="1000000" onchange="hitungCicilan()" onblur="checkPinjaman()" <?= $cop_flexibleamount[0]['value'] != "1" ? '' : ''; ?> /> <!--- script baru-->
						<span id="loan1_read"><?php echo 'Rp. ' . price_format($refreshbag['loan1']); ?></span>&nbsp;
						<span id="loan1_terbilang" data-format="terbilang"><?= $refreshbag['loan1']; ?></span>
						<!-- <input type="hidden" id="pjm_loan1" name="pjm_loan1" value="<?= $refreshbag['loan1']; ?>" /> -->
					<?php } ?>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="checkbox" id="ang_autodebet" name="ang_autodebet" value="IYA" />
					<label style="color: red;"><strong>Loan 1 Only</strong></label>
				</td>
			</tr>
			<tr class="a">
				<td><label>Loan 2</label></td>
				<td>:</td>
				<td>
					<?php if (empty($refreshbag)) { ?>
						<input type="text" id="pjm_loan2" name="pjm_loan2" value="<?= $prospect_detail['loan2']; ?>" placeholder="<?php echo $prospect_detail['loan2']; ?>" step="100000" min="0" onchange="hitungCicilan()" onblur="checkPinjaman()" <?= $cop_flexibleamount[0]['value'] != "1" ? '' : ''; ?> /> <!--- script baru-->
						<span id="loan2_read"><?php echo 'Rp. ' . price_format($prospect_detail['loan2']); ?></span>&nbsp;
						<span id="loan2_terbilang" data-format="terbilang"><?= $prospect_detail['loan2']; ?></span>
						<!-- <input type="hidden" id="pjm_loan2" name="pjm_loan2" value="<?= $prospect_detail['loan2']; ?>" /> -->
					<?php } else { ?>
						<input type="text" id="pjm_loan2" name="pjm_loan2" value="<?= $refreshbag['loan2']; ?>" placeholder="<?php echo $refreshbag['loan2']; ?>" step="100000" min="0" onchange="hitungCicilan()" onblur="checkPinjaman()" <?= $cop_flexibleamount[0]['value'] != "1" ? '' : ''; ?> /> <!--- script baru-->
						<span id="loan2_read"><?php echo 'Rp. ' . price_format($refreshbag['loan2']); ?></span>&nbsp;
						<span id="loan2_terbilang" data-format="terbilang"><?= $refreshbag['loan2']; ?></span>
						<!-- <input type="hidden" id="pjm_loan2" name="pjm_loan2" value="<?= $refreshbag['loan2']; ?>" /> -->
					<?php } ?>
				</td>
			</tr>
			<tr class="a">
				<td><label>Loan 3</label></td>
				<td>:</td>
				<td>
					<?php if (empty($refreshbag)) { ?>
						<span id="loan3_read"><?php echo 'Rp. ' . price_format($prospect_detail['loan3']); ?></span>
						<span id="loan3_terbilang" data-format="terbilang"><?= $prospect_detail['loan3']; ?></span>
						<input type="hidden" id="pjm_loan3" name="pjm_loan3" value="<?= $prospect_detail['loan3']; ?>" />
					<?php } else { ?>
						<span id="loan3_read"><?php echo 'Rp. ' . price_format($refreshbag['loan3']); ?></span>
						<span id="loan3_terbilang" data-format="terbilang"><?= $refreshbag['loan3']; ?></span>
						<input type="hidden" id="pjm_loan3" name="pjm_loan3" value="<?= $refreshbag['loan3']; ?>" />
					<?php } ?>
				</td>
			</tr>
			<tr class="hide">
				<td colspan="3">
					<p>RDF = <span id="rdf_test"></span></p>
					<p>loan1 = <span id="loan1_test"></span></p>
					<p>loan2 = <span id="loan2_test"></span></p>
					<p>loan3 = <span id="loan3_test"></span></p>
				</td>
			</tr>
			<tr class="b">
				<td><label>Jumlah pinjaman yang diinginkan (Terbilang)</label></td>
				<td>:</td>
				<td><span id="bilang_pinjamincome" style="color:#C58A32;font-weight:bold;"></span></td>
			</tr>
			<tr class="a">
				<td><label>Jumlah Dana yang Diterima (Terbilang)</label></td>
				<td>:</td>
				<td><span id="bilang_totalpenerimaandana" style="color:#E131EC;;font-weight:bold;"></td>
			</tr>

			<tr class="b">
				<td><label>Jangka waktu pinjaman<span class="smallinfo">(bulan)</span></label></td>
				<td>:</td>
				<td>
					<div id="tenor_option">-Choose MSC-</div>
				</td>
			</tr>

			<tr class="a">
				<td><label>Bunga Flat / Bulan</label></td>
				<td>:</td>
				<td><input type="number" id="ben_bunga" name="ben_bunga" value="0" onchange="hitungCicilan()" style="width:50px" max="100" min="0" step="0.01" readonly="true" /> %</td>
			</tr>

			<tr class="b" style="display: none;">
				<td><label>Outstanding</label></td>
				<td>:</td>
				<td>
					<input type="text" id="ben_os" name="ben_os" value="0" onchange="hitungCicilan()" />
					<span id="ben_osread"></span>
				</td>
			</tr>

			<tr class="a" style="display: none;">
				<td><label>Provisi</label></td>
				<td>:</td>
				<td><input type="number" id="ben_provisi" name="ben_provisi" value="0.0" onchange="hitungCicilan()" style="width:50px" min="0" max="100" step="0.1" readonly="true" /> %</td>
			</tr>

			<tr class="b">
				<td><label>Total Bunga</label></td>
				<td>:</td>
				<td><input type="text" id="ben_totalbunga" name="ben_totalbunga" value="0" readonly="true" />
					<span id="ben_totalbunga_terbilang" style="color:#C58A32;font-weight:bold;"></span>
				</td>
			</tr>

			<tr class="a">
				<td><label>Bunga Bulanan</label></td>
				<td>:</td>
				<td><input type="text" id="ben_totalbungabulanan" name="ben_totalbungabulanan" value="0" readonly="true" />
					<span id="ben_totalbungabulanan_terbilang" style="color:#C58A32;font-weight:bold;"></span>
				</td>
			</tr>

			<tr class="b">
				<td><label>Bunga Harian</label></td>
				<td>:</td>
				<td><input type="text" id="ben_totalbungaharian" name="ben_totalbungaharian" value="0" readonly="true" />
					<span id="ben_totalbungaharian_terbilang" style="color:#C58A32;font-weight:bold;"></span>
				</td>
			</tr>

			<tr class="a">
				<td><label>Administrasi</label></td>
				<td>:</td>
				<td><input type="text" id="ben_materai" name="ben_materai" value="0" onchange="hitungCicilan()" class="smallInput" maxlength="8" readonly="true" /></td>
			</tr>

			<tr class="b">
				<td><label>Jumlah cicilan yang diinginkan</label></td>
				<td>:</td>
				<td>Rp.&nbsp<input type="text" id="ben_cicilanincome" name="ben_cicilanincome" value="0" readonly />
					<span id="bilang_cicilanincome" style="color:#C58A32;font-weight:bold;"></span>
				</td>
			</tr>

			<tr class="b">
				<td><label>Cycle Date </label></td>
				<td>:</td>
				<td><input type="text" id="tgl_cycle" name="tgl_cycle" value="<?= DATE('Y-m-d') ?>" readonly="true" /></span></span>
				</td>
			</tr>

		</table>

	</div>

	<!-- Form Data Pinjaman -->

	<div id="frmPinjeman" class="formContainer" style="display:none">
		<table class="appform" style="display: none;">
			<tr>
				<th colspan="3">Data Pinjaman Lain</th>
			</tr>

			<tr class="a">
				<td><label>Nama Bank</label></td>
				<td>:</td>
				<td><input type="text" id="pjm_fullname" name="pjm_fullname" value="" /></td>
			</tr>

			<tr class="b">
				<td><label>Jenis Pinjaman<span class="smallInfo">(Pilih salah satu bila ada)</span></label></td>
				<td>:</td>
				<td>
					<p><input type="radio" name="pjm_genderopt" value="M" /> Mobil / Motor </p>
					<p><input type="radio" name="pjm_genderopt" value="R" /> Rumah </p>
					<p><input type="radio" name="pjm_genderopt" value="K" /> Kredit Tanpa Agunan </p>
					<p><input type="radio" name="pjm_genderopt" value="OTHER" /> Lainnya:<span class="smallInfo">(sebutkan)</span> <input type="text" id="pjm_other" name="pjm_other" value="" /></p>&nbsp

				</td>
			</tr>

			<tr class="a">
				<td><label>Sejak Tahun (YYYY)</label></td>
				<td>:</td>
				<td><input type="text" id="pjm_year" name="pjm_year" value="" maxlength="4" /></td>
			</tr>

			<tr class="b">
				<td><label>Lama pinjaman</label></td>
				<td>:</td>
				<td><input type="number" id="pjm_resideyear" name="pjm_resideyear" value="" class="smallInput" /> &nbsp Tahun &nbsp <input type="number" id="pjm_residemonth" name="pjm_residemonth" value="" class="smallInput" min="0" max="12" /> &nbsp Bulan</td>
			</tr>

			<tr class="a">
				<td><label>Jumlah pinjaman</label></td>
				<td>:</td>
				<td>Rp.&nbsp<input type="text" id="pjm_pinjcount" name="pjm_pinjcount" value="" onblur="formatThis(this)" /></td>
			</tr>

			<tr class="b">
				<td><label>Total angsuran bulanan</label></td>
				<td>:</td>
				<td>Rp.&nbsp<input type="text" id="pjm_angsuran" name="pjm_angsuran" value="" onblur="formatThis(this)" /></td>
			</tr>

			<tr class="a">
				<td><label>Nomor Kartu Kredit yang telah Anda miliki, jika ada</label></td>
				<td>:</td>
				<td><input type="text" id="pjm_nokartu" name="pjm_nokartu" value="<?= @$prospect_detail['card_number']; ?>" /></td>
			</tr>

		</table>

		<!-- Pencairan Pinjaman -->
		<table class="appform">
			<tr>
				<th colspan="3">Data Pencairan Pinjaman</th>
			</tr>

			<tr class="a">
				<td>
					<input type="radio" id="pjm_bankopt1" name="pjm_bankopt" value="UOB" />
					<label>No Rekening UOB</label>
				</td>
				<td>:</td>
				<td><input type="text" name="pjm_accnopermata" id="pjm_accnopermata" value="" readonly /></td>
			</tr>
			<tr class="b">
				<td>
					<input type="radio" id="pjm_bankopt2" name="pjm_bankopt" value="OTHER_BANK" />
					<label>No Rekening Bank Lain</label>
				</td>
				<td>:</td>
				<td>
					<input type="text" name="pjm_accnoother" id="pjm_accnoother" value="" readonly="" onkeypress="return hanyaAngka(event)" />
					&nbsp;|&nbsp; Nama Bank
					<!--<input type="text" name="pjm_banknameother" id="pjm_banknameother" value="" style="width:250px" readonly="" />-->
					<select class="eas" name="pjm_banknameother" id="pjm_banknameother" style="width:250px; float:none">
						<option value="">Bank List</option>
						<?php if (!empty($list_bank)) : ?>
							<?php foreach ($list_bank as $row) { ?>
								<option value="<?php echo $row['bank_code'] ?>"><?php echo $row['bank_code']; ?> - <?php echo $row['bank_name'] ?></option>
							<?php } ?>
						<?php endif ?>
					</select>
				</td>
			</tr>

			<tr class="a">
				<td><label>Nama Penerima Sesuai Buku Tabungan</label></td>
				<td>:</td>
				<td><input type="text" name="pjm_accname" id="pjm_accname" value="" style="width:220px" onkeypress="return event.charCode < 48 || event.charCode  > 57" /></td>
			</tr>

			<tr class="a">
				<td>Deviasi</td>
				<td>:</td>
				<td>
					<select id="status_form" name="status_form" data-required="1">
						<option value="">-Pilih-</option>
						<option value="DEVIASI">Deviasi</option>
					</select>
				</td>
			</tr>

			<tr class="a">
				<td>Aktivasi Recarding </td>
				<td>:</td>
				<td>
					<select id="ben_otherjasa" name="ben_otherjasa" data-required="1">
						<option value="">-Pilih-</option>
						<option value="AKTIVASI">Aktivasi</option>
					</select>
				</td>
			</tr>

			<tr class="b hide">
				<td><label>Cabang Pembukaan Rekening</label></td>
				<td>:</td>
				<td><input type="text" name="pjm_accbranch" id="pjm_accbranch" value="" style="width:220px" /></td>
			</tr>

			<tr class="hide">
				<th colspan="3">Informasi Kartu</th>
			</tr>

			<tr class="a hide">
				<td><label>Nomer Kartu Kredit</label></td>
				<td>:</td>
				<td><input type="text" name="pjm_cardno" id="pjm_cardno" value="<?= @$prospect_detail['card_number_basic']; ?>" style="width:220px" maxlength="16" onkeypress="return hanyaAngka(event)" /></td>
			</tr>
		</table>

		<table class="appform" style="display: none;">
			<tr>
				<th colspan="3">Data Pencairan Pihak Ke tiga</th>
			</tr>

			<tr class="b">
				<td>
					<label>Nama Pihak Ke Tiga yang dipilih (Wajib diisi)</label>
				</td>
				<td>:</td>
				<td>
					<input type="text" name="pjm_name" id="pjm_name" value="" style="width:150px" readonly="true" />
					&nbsp;|&nbsp; No. Rekening Pihak Ketiga (Wajib diisi)
					<input type="text" name="pjm_norek" id="pjm_norek" value="" style="width:150px" readonly="true" />
					<span class="cmpInfo">(* hanya untuk formulir multiguna) </span>
				</td>
			</tr>


			<tr class="a">
				<td>
					<label>Nama Bank Penerima (Wajib diisi)</label>
				</td>
				<td>:</td>
				<td>
					<input type="text" name="pjm_bankname" id="pjm_bankname" value="" style="width:150px" readonly />
					&nbsp|&nbsp Cabang Pembuka (Wajib diisi)
					<input type="text" name="pjm_cabang" id="pjm_cabang" value="" style="width:150px" readonly />
					<span class="cmpInfo">(* hanya untuk formulir Multiguna) </span>
				</td>
			</tr>

			<tr class="b">
				<td>
					<label>Nama Pemilik Rekening (Wajib diisi)</label>
				</td>
				<td>:</td>
				<td>
					<input type="text" name="pjm_namerek" id="pjm_namerek" value="" style="width:150px" readonly />
					&nbsp|&nbsp Nomor Telepon Pihak Ketiga (Wajib diisi)
					<input type="text" id="pjm_telarea" name="pjm_telarea" value="" maxlength="4" style="width:50px" readonly />
					-
					<input type="text" id="pjm_telpon" name="pjm_telpon" value="" readonly />
					<span class="cmpInfo">(* hanya untuk formulir Multiguna) </span>
				</td>
			</tr>

		</table>

	</div>

	<!-- Form Pembayaran Angsuran -->

	<div id="frmAngsuran" class="formContainer" style="display:none">
		<table class="appform">
			<tr>
				<th colspan="3">Pembayaran Angsuran / Fasilitas Auto Debit</th>
			</tr>

			<tr class="a">
				<td><input type="checkbox" id="ang_autodebet2" name="ang_autodebet2" value="1" /> Autodebet rekening saya dengan nomor rekening</td>
				<td>:</td>
				<td><input type="text" id="ang_rekening" name="ang_rekening" value="" /></td>
			</tr>
			<tr class="b">
				<td>CIF</td>
				<td>:</td>
				<td><input type="text" id="ang_cif" name="ang_cif" value="" /></td>
			</tr>
			<tr class="a">
				<td>Nama Pemilik Rekening(harus atas nama Pemohon sendiri)</td>
				<td>:</td>
				<td><input type="text" id="ang_accname" name="ang_accname" value="" /></td>
			</tr>
			<tr class="b">
				<td>Cabang Pembukaan Rekening</td>
				<td>:</td>
				<td><input type="text" id="ang_cabang" name="ang_cabang" value="" /></td>
			</tr>
			<tr class="a">
				<td><label>Tipe Pembayaran ( Khusus Pengajuan dengan Kartu Kredit )</label></td>
				<td>:</td>
				<td>
					<input type="radio" id="ang_paytype1" name="ang_paytype" value="MIN" /> Pembayaran Minimum
					<input type="radio" id="ang_paytype2" name="ang_paytype" value="FULL" /> Pembayaran Penuh &nbsp;|&nbsp;
					<a onclick="resetPaytype();" style="cursor:pointer"> Reset </a>
				</td>
			</tr>
		</table>
	</div>
	<!-- Form pickup -->
	<div id="frmPickup" class="formContainer" style="display:none">
		<table class="appform">
			<tr>
				<th colspan="3">Pickup Option</th>
			</tr>

			<tr class="a">
				<td><label>Pickup Address</label></td>
				<td>:</td>
				<td>
					<p>
						<input type="radio" name="pickup_opt" id="pickup_opt1" value="SOC" /> Alamat KTP&nbsp
						<input type="radio" name="pickup_opt" id="pickup_opt2" value="BIL" /> Alamat Tinggal/Penagihan&nbsp
						<input type="radio" name="pickup_opt" id="pickup_opt3" value="OFF" /> Alamat Kantor&nbsp
						<input type="radio" name="pickup_opt" id="pickup_opt4" value="EML" /> Email&nbsp
					</p>
				</td>
			</tr>

			<tr class="b">
				<td><label>Alamat Pickup</label></td>
				<td>:</td>
				<td>
					<textarea id="pku_addr" name="pku_addr" readonly></textarea>
				</td>
			</tr>

			<tr class="a">
				<td><label>Kode Pos</label></td>
				<td>:</td>
				<td>
					<input type="text" name="pku_zipcode" id="pku_zipcode" value="" maxlength="5" readonly />
				</td>
			</tr>

			<tr class="b">
				<td><label>Kelurahan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="pku_kelurahan" name="pku_kelurahan" value="" readonly />
				</td>
			</tr>

			<tr class="a">
				<td><label>Kecamatan</label></td>
				<td>:</td>
				<td>
					<input type="text" id="pku_kecamatan" name="pku_kecamatan" value="" readonly />
				</td>
			</tr>

			<tr class="b">
				<td><label>RT / RW</label></td>
				<td>:</td>
				<td><input type="text" id="pku_rt" name="pku_rt" value="" class="smallInput" maxlength="5" readonly /> / <input type="text" id="pku_rw" name="pku_rw" value="" class="smallInput" maxlength="5" readonly /></td>
			</tr>

			<tr class="a">
				<td><label>Kota</label></td>
				<td>:</td>
				<td>
					<input type="text" id="pku_city" name="pku_city" list="cities" readonly />
				</td>
			</tr>

			<tr class="a" style="display: none;">
				<td><label>Kotamadya / Kabupaten</label></td>
				<td>:</td>
				<td><input type="text" id="pku_kabupaten" name="pku_kabupaten" value="" readonly /></td>
			</tr>

			<tr class="b">
				<td><label>Date Pickup (YYYY-MM-DD)</label></td>
				<td>:</td>
				<td>
					<input type="text" id="pku_date" name="pku_date" placeholder="YYYY-MM-DD" value="" readonly />
					<input type="button" id="pku_datepicker" name="pku_datepicker" value="Date Planner" class="btn-contacted" />
					<input type="button" id="pku_datepickerclear" name="pku_datepickerclear" value="Clear" class="btn-uncontacted" />
				</td>
			</tr>

			<tr class="a">
				<td><label>Email</label></td>
				<td>:</td>
				<td><input type="text" id="pku_email" name="pku_email" value="" readonly /></td>
			</tr>

			<!--		
		<tr class="b">
			<td><label>Courier</label></td>
			<td>:</td>
			<td>
				<input type="text" id="pku_courier" name="pku_courier" readonly>
			<?php /* foreach($list_kurir as $kurir) {?>
					<option value="<?php echo $kurir['id_kurir']; ?>"><?php echo $kurir['name']; ?></option>
			<?php } */ ?>
			</td>
		</tr>
-->

			<tr class="b">
				<td><label>Pickup Notes</label></td>
				<td>:</td>
				<td><textarea id="pku_notes" name="pku_notes" maxlength="200"></textarea></td>
			</tr>

			<tr class="b" style="display: none;">
				<td><label>Memo 2</label></td>
				<td>:</td>
				<td><textarea id="pku_memo" name="pku_memo" maxlength="200"></textarea></td>
			</tr>

			<tr class="a">
				<td><label>Agree Notes</label></td>
				<td>:</td>
				<td><textarea id="agree_notes2" name="agree_notes2" maxlength="300"></textarea></td>
			</tr>
		</table>

		<table class="appform" style="display: none;">
			<tr>
				<th colspan="2">Program Tambahan</th>
			</tr>
			<tr class="a">
				<td>Program 1</td>
				<td>
					<select name="program_1">
						<option value="">- Program -</option>
						<?php if (count($list_program) > 0) { ?>
							<?php foreach ($list_program as $program) { ?>
								<option value="<?php echo $program['idx']; ?>"><?php echo $program['program_name']; ?></option>
						<?php }
						} ?>
					</select>
				</td>
			</tr>

			<tr class="b">
				<td>Program 2</td>
				<td>
					<select name="program_2">
						<option value="">- Program -</option>
						<?php if (count($list_program) > 0) { ?>
							<?php foreach ($list_program as $program) { ?>
								<option value="<?php echo $program['idx']; ?>"><?php echo $program['program_name']; ?></option>
						<?php }
						} ?>
					</select>
				</td>
			</tr>

			<tr class="a">
				<td>Program 3</td>
				<td>
					<select name="program_3">
						<option value="">- Program -</option>
						<?php if (count($list_program) > 0) { ?>
							<?php foreach ($list_program as $program) { ?>
								<option value="<?php echo $program['idx']; ?>"><?php echo $program['program_name']; ?></option>
						<?php }
						} ?>
					</select>
				</td>
			</tr>
		</table>
	</div>

	<div id="navi_box">
		<dt>
			<input type="button" class="button hide" name="prev" id="prev" value="<< Prev" onclick="prevTab();" /> &nbsp
			<input type="button" class="button hide" name="next" id="next" value="Next >>" onclick="nextTab();" /> &nbsp
		</dt>
		<dt>
			<input type="button" class="button hide" name="savedata" id="savedata" value="Save" onclick="savedata_pl('<?= site_url(); ?>/autofill/savedata_pl');" /> &nbsp
			<input type="button" class="button hide" name="loaddata" id="loaddata" value="Load" onclick="load_saveddata('<?= site_url(); ?>/autofill/loaddata/pl', 'pl');" /> &nbsp
		</dt>
	</div>

	<div id="submit_box">
		<dt>
			<input type="button" class="btn-contacted" id="dosubmit" name="dosubmit" value="Submit" />
			&nbsp
			<input type="button" class="btn-uncontacted" id="docancel" name="docancel" value="Cancel" />
		</dt>
	</div>

	<div class="floatingRight">
		<pre id="question">
</pre>
		<p>Klik Untuk Close</p>
	</div>

	<!-- Hidden Information -->
	<input type="hidden" id="id_campaign" name="id_campaign" value="<?php echo $prospect_detail['id_campaign']; ?>" />
	<input type="hidden" id="id_product" name="id_product" value="<?php echo $id_product; ?>" />
	<input type="hidden" id="no_contacted" name="no_contacted" value="<?php echo $no_contacted; ?>" />
	<input type="hidden" id="id_calltrack" name="id_calltrack" value="<?php echo $id_calltrack; ?>" />
	<input type="hidden" id="xtenor" name="xtenor" value="0" />
	<!--<input type="hidden" id="xben_pinjamopt" name="xben_pinjamopt" value="<?php echo $productcode['tujuan_pembiayaan']; ?>" />-->
	<input type="hidden" id="curtab" name="curtab" value="" />
	<input type="hidden" id="map_hp1" name="map_hp1" value="<?= @$prospect_detail['hp1']; ?>" />
	<input type="hidden" id="map_hp2" name="map_hp2" value="<?= @$prospect_detail['hp2']; ?>" />
	<input type="hidden" id="zone_id" name="zone_id" value="" />
	<input type="hidden" id="buy_camptype" name="buy_camptype" value="COP" />
	<input type="hidden" id="appointment_status" name="appointment_status" value="" disabled />

	<input type="hidden" id="typexsell" name="typexsell" value="<?php echo $multioffers; ?>" />
	<input type="hidden" id="xsellidx" name="xsellidx" value="<?= $xsellidx['idx']; ?>" />
	<input type="hidden" id="idxsell" name="idxsell" value="<?php echo $multiidx; ?>" />

	<?php if (empty($refreshbag)) : ?>
		<input type="hidden" id="available_credit" name="available_credit" value="<?= $prospect_detail['available_credit'] ?>" />
	<?php else : ?>
		<input type="hidden" id="available_credit" name="available_credit" value="<?= $refreshbag['available_credit'] ?>" />
	<?php endif; ?>

</form>

<div id="confirmationBox" class="confirmationBox" style="width:300px">
	<div>Hari Ini <span style="font-weight:bold"><span id="dayofweek"></span>,<?php echo DATE('d M Y') . ' Jam ' . DATE('H:i'); ?></span> <br />Bapak/Ibu <span style="font-weight:bold" id="conf_namalengkap"></span>
		Setuju untuk mengaktifkan Program Cash On Phone dari Bank UOB Indonesia dengan total <span id="conf_pinjaman" style="font-weight:bold"></span> <br /> dan cicilan Rp. <span id="conf_cicilan" style="font-weight:bold"></span> selama <span id="conf_tenor" style="font-weight:bold"></span> bulan.<br />
		apakah Bapak/Ibu benar-benar setuju untuk pengaktifan tersebut ?
	</div>
	<dt>
		<input type="button" id="confirmationOk" name="confirmationOk" value="Agree" class="button" onclick="confirmationOk(this);">
	</dt>
</div>

<script type="text/javascript" src="<?php echo base_url() ?>/component/js/saving_module.js">
	< /!script> <
	script type = "text/javascript"
	src = "<?php echo base_url() ?>/component/js/terbilang.js" >
		<
		/>