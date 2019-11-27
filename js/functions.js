
$(window).resize(function() {
	resizeAll();
});

$(document).ready(function( event, ui ) {
	resizeAll();
	reload_liste_tache('user');

	reload_liste_tache('workstation');

	reload_liste_of();

} );

function setWorkstation(wsid) {

	$("#search_workstation").val(wsid);
	$('ul#list-workstation li,ul#list-workstation li').removeClass('active');
	$('ul#list-workstation li[ws-id='+wsid+']').addClass('active');

	reload_liste_tache('workstation');

}

function changeUser(fk_user) {

	$("#search_user").val(fk_user);
	$("#user-name").html( $('li[user-id='+fk_user+']').attr('login') );
	reload_liste_tache('user');
	reload_liste_tache('workstation');
	reload_liste_of();

}


function resizeAll() {


	var doc_width = $(window).width();
	var doc_height = $(window).height();

	if(doc_width>768) {

		nb_user = $('#select-user-list>li').length;

		if(nb_user>10) {

			if(doc_width>800 && nb_user>30) {
				$('#select-user-list').width( 600 );
				$('#select-user-list>li').removeClass('col-md-6').addClass('col-md-4').width(160);
			}
			else if(doc_width>500) {
				$('#select-user-list').width( 400 );
				$('#select-user-list>li').removeClass('col-md-4').addClass('col-md-6').width(160);
			}
		}

		if($('#select-user-list').height()>doc_height) {
			$('#select-user-list').css('overflow-y', 'scroll').height( doc_height - 200 );
		}

	}
	else {
		$('#select-user-list').css('height', null).css('overflow-y',null);
	}
}

function start_task(id_task,onglet){

	$li = $("#liste_tache_"+onglet+" > #"+id_task);

	$li.find('.start').hide();
	$li.find('.pause').show();
	$li.find('.close').show();

	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   put:'start_task'
			   ,id : id_task
			   ,json : 1
		}
	})
	.then(function (data){
		//console.log(data);
		if(data.result == 'OK'){
			$li.find(".pause span[start-time]").html(data.tasklist_time_start);
			$li.addClass('running');
		}
	});
}

function getTimeSpent(id_task, action){

	var res = '00:00';

	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   get:'time_spent'
			   ,id : id_task
			   ,action : action
			   ,json : 1
		}
	})
	.then(function (data){
		res = data;
	});
	return res;
}

function aff_popup(id_task,onglet,action){

	$("#confirm-add-time").modal({show: true});

	timespent = getTimeSpent(id_task,action);
	TTime = timespent.split(":");
	hour = TTime[0];
	minutes = TTime[1];
	$('#heure').val(hour);
	$('#minute').val(minutes);

	$('#valide_popup').unbind().click(function(event, ui){

		hour = $('#heure').val();
		minutes = $('#minute').val();

		if(action == 'stop'){
			stop_task(id_task,onglet,hour,minutes);
		}
		else{
			close_task(id_task,onglet,hour,minutes);
		}

		$('#confirm-add-time').modal('hide');
	});

}

function stop_task(id_task,onglet,hour,minutes){

	$li = $('#liste_tache_'+onglet+' > #'+id_task);
	$li.find('.start').show();
	$li.find('.pause').hide();
	$li.find('.close').show();

	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   put:'stop_task'
			   ,id : id_task
			   ,hour : hour
			   ,minutes : minutes
			   ,json : 1
			   ,id_user_selected : $('#search_user').val()
		}
	})
	.then(function (data){
		//console.log(data);
		refresh_time_spent($li, data);
		$li.removeClass('running');
	});
}

function close_task(id_task,onglet,hour,minutes){

	/*$("#liste_tache_"+onglet+" > #"+id_task).find('.start').hide();
	$("#liste_tache_"+onglet+" > #"+id_task).find('.pause').hide();
	$("#liste_tache_"+onglet+" > #"+id_task).find('.close').hide();*/
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   put:'close_task'
			   ,id : id_task
			   ,hour : hour
			   ,minutes : minutes
			   ,json : 1
			   ,id_user_selected : $('#search_user').val()

		}
	})
	.then(function (data){
		//alert(onglet);
		//reload_liste_tache(onglet);
		ajax_get_liste_task(0,'user');
		ajax_get_liste_task(0,'workstation');
		ajax_get_liste_task(0,'of');
		// reload_liste_of();
		reload_liste_tache('of');
	});
}

function refresh_time_spent($obj, data)
{
	$obj.find('span[rel=progress]').text(data.progress);
	$obj.find('span[rel=spent_time]').text(data.time);
	$obj.find('span[rel=calculate_progress]').text(data.progress_calculated);

}

function openOF(fk_of, numero) {

	reload_liste_tache('of', fk_of);
	//reload_liste_of();

	/*inutile déjà fait par le framework
	 * var currentPage = $(':mobile-pagecontainer').pagecontainer( "getActivePage" ).attr('id');
	if(currentPage!='list-task-of')
	 */
	$('ul#liste-of li').removeClass('active');
	$('ul#liste-of li[fk-of='+fk_of+']').addClass('active');


	_draw_of_product(fk_of);

	$('#menu-tasklist ul[role=tablist] a[href="#list-of"]').tab('show');

}

function _draw_of_product(fk_of){

	$.ajax({
		url:'ajax/interface.php'
		,data:{
			get :'task-product-of'
			,fk_of: fk_of
		}
		,dataType:'json'
	}).done(function(data){

		$('#list-task-of table.product-list').remove();

		var needed = false;
		var tomake=false;

		if(data.productOF.length>0) {

			reload_tomake_needed(data);

			$('#retour-atelier').click(function() {

				var $bt = $(this);

				var originalText = $bt.text();
				$bt.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> ...");

				TLine=[];
				$('input[rel=prod-qty-used]').closest('tr').each(function(i,item) {
					TLine.push({
						'lineid':$(item).find('input[rel=prod-qty-used]').attr('line-id')
						,'qty_use':$(item).find('input[rel=prod-qty-used]').val()
						,'qty_non_compliant':$(item).find('input[rel=prod-qty-non-compliant]').val()
					});
				});

				$.ajax({
					url:'ajax/interface.php'
					,data:{
						put :'task-product-of'
						,fk_of: fk_of
						,TLine : TLine
						,json:1
					}
					,method:'post'
					,dataType:'json'
				}).done(function(datatask){
					console.log(datatask);

					reload_liste_tache('of', fk_of);
					_draw_of_product(fk_of);
					window.alert('Lignes modifiées');

					$bt.html(originalText);
				});
			});

		}

		if(data.productTask.length>0) {
			for(taskid in data.productTask) {
				$('#task_list_'+taskid+' collapse.collapse').preprend('<table id="product-list-task-'+taskid+'" class="table table-striped table-condensed product-list"></table> ');
				$table = $('table#product-list-task-'+taskid);
				$table.append('<tr><th>Produit</th><th>Quantité</th><th>#</th></tr>');

				for(x in data.productTask[taskid]) {

					line = data.productTask[taskid][x];

					$table.append('<tr><td>'+line.label+'</td><td>'+line.qty+'</td><td></td></tr>');

				}

			}
		}



	});

}

function reload_tomake_needed(data){
	$table = $('<table id="product-list-of" class="table table-striped table-condensed product-list"></table> ');

	$table.append('<thead><tr><th class="col-md-8">Produit Nécessaire</th><th class="col-md-2">Quantité prévue</th><th class="col-md-2">Utilisée</th></tr></thead><tbody></tbody>');
	needed = false;
	tomake = false;
	for(x in data.productOF) {

		line = data.productOF[x];

		if(line.type == 'NEEDED'){
			$tr = $('<tr />');
			$tr.append('<td>'+line.label+'</td>');
			$tr.append('<td>'+line.qty+'</td>');

			$tr.append('<td><input rel="prod-qty-used" line-id="'+line.lineid+'" type="text" value="'+line.qty_used+'" size="5" /></td>');
			$table.find('tbody').append($tr);

			needed = true;
		}
	}

	$table2 = $('<table id="product-list-of-tomake" class="table table-striped table-condensed product-list"></table> ');
	console.log(data.conf.global.OF_MANAGE_NON_COMPLIANT, data.of.status);
	if(data.conf.global.OF_MANAGE_NON_COMPLIANT ==1&& (data.of.status=='OPEN' || data.of.status == 'CLOSE')){
		$compliantTHead =  '<th class="col-md-2">Conforme</th><th class="col-md-2">Non Conforme</th>';
	} else {
		$compliantTHead = '<th class="col-md-2">Fabriquée</th>';
	}

	$table2.append('<thead><tr><th class="col-md-8">Produit Fabriqué</th><th class="col-md-2">Quantité prévue</th>'+$compliantTHead+'</tr></thead><tbody></tbody>');

	for(x in data.productOF) {

		line = data.productOF[x];

		if(line.type == 'TO_MAKE'){
			$tr = $('<tr />');
			$tr.append('<td>'+line.label+'</td>');
			$tr.append('<td>'+line.qty+'</td>');

			$tr.append('<td><input rel="prod-qty-used" line-id="'+line.lineid+'" type="text" value="'+line.qty_used+'" size="5" /></td>');

			if(data.conf.global.OF_MANAGE_NON_COMPLIANT ==1&& (data.of.status=='OPEN' || data.of.status == 'CLOSE')){
				$tr.append('<td><input rel="prod-qty-non-compliant" line-id="'+line.lineid+'" type="text" value="'+line.qty_non_compliant+'" size="5" /></td>');
			}
			$table2.find('tbody').append($tr);

			tomake = true;
		}
	}

	if(needed || tomake) {
		if(data.conf.global.OF_MANAGE_NON_COMPLIANT ==1&& (data.of.status=='OPEN' || data.of.status == 'CLOSE') && tomake){
			$colspan = 4;
		}else $colspan = 3;
		$table2.append('<tr><td align="right" colspan="'+$colspan+'"><button class="btn btn-default" id="retour-atelier">Enregistrer</button></td></tr>');
		$('#list-task-of div#liste_tache_of').before($table);
		$('#list-task-of div#liste_tache_of').before($table2);

	}
}

function reload_liste_of() {

	var conf = '';
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   get:'of_liste'
			   ,json : 1
			   ,fk_user: $('#search_user').val()
		}
	})
	.then(function (data){
		//console.log(data);
		conf = data['conf'];
		$li = $('ul#liste-of');

		$('#list-task-of table.product-list').remove();
		$li.empty();

		for(x in data) {
			if(x == 'conf') continue;
			var OF = data[x];
			var more = '';
			if(OF.date_lancement > 0) {
				var date = new Date(OF.date_lancement*1000);
				more = ' ('+date.getDay()+'/'+date.getMonth()+'/'+date.getFullYear()+') ';
			}
			$li.append('<li class="list-group-item" data-rank="'+OF.rank+'" data-launching_date="'+OF.date_lancement+'" fk-of="'+OF.fk_of+'"><a href="javascript:openOF('+OF.fk_of+',\''+data[x]+'\')">'+OF.label+ ' [' + OF.statut +']'+more+'</a>&nbsp;&nbsp;&nbsp;'+printShowDocumentsIcon(OF.fk_of)+'</li>');

		}

        if (conf.global.OF_RANK_PRIOR_BY_LAUNCHING_DATE) { //Si la conf est activé on doit pouvoir réordonner en drag & drop
            $('ul#liste-of').sortable();
            $('ul#liste-of').on("sortupdate", function (event, ui) { // On modifie le rang

                let fk_of = $(ui.item).attr('fk-of');
                let date_lancement = $(ui.item).data('launching_date');

                if (date_lancement > 0) {

                    let TOfs = $('li[data-launching_date="' + date_lancement + '"');
                    if (TOfs.length > 0) {
                        TOfs.each(function (key, liOf) {
                            if (fk_of == $(liOf).attr('fk-of')) {
                                $.ajax({
                                    url: "ajax/interface.php",
                                    dataType: "json",
                                    crossDomain: true,
                                    async: false,
                                    data: {
                                        put: 'set-of-rank'
                                        , json: 1
                                        , fk_of: fk_of
                                        , new_rank: (key + 1)
                                    }
                                });
                            }
                        });
                    }
                }
            });
        }
    });

}
function printShowDocumentsIcon(fk_of) {

	let div = '';
	$.ajax({
		url: "ajax/interface.php",
		dataType: "html",
		crossDomain: true,
		async: false,
		data: {
			get: 'of-documents'
			, id: fk_of
		}
	}).done(function(data){
		div = '<div id="doc-of-'+fk_of+'" style="display: none;">'+data+'</div>';
	});

	return '<span class="hover-cursor" onclick="showDocuments('+fk_of+')"><i class="fa fa-download" aria-hidden="true"></i></span>'+div;
}
function showDocuments(fk_of) {
	if(!$('#doc-of-'+fk_of).hasClass('ui-dialog-content')) $('#doc-of-'+fk_of).dialog({minWidth: 500});
	$('#doc-of-'+fk_of).dialog('open');
}
function reload_liste_tache(type, id){

	switch(type){

		case "user": //Utilisateurs
			if(id==null) id = $('#search_user').val();

			break;
		case "workstation": //Postes de travail
			if(id==null) id = $('#search_workstation').val();

			break;
		case "of": //Ordre de fabrication

			if(id==null) id = $('#liste-of>li.active').attr('fk-of');
			//if(id==null) id = $('#search_of').val();
			break;
	}

	ajax_get_liste_task(id,type);
}

function ajax_get_liste_task(id,type){

	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   get:'task_liste'
			   ,id : id
			   ,type : type
			   ,json : 1
			   ,fk_user: $('#search_user').val()
		}
	})
	.then(function (data){
		//console.log(data);
		refresh_liste_tache(data,type);
	});
}

function refresh_liste_tache(data,type){

	vider_liste(type);

	$.each(data,function(i,task){
		var clone = $('#task_list_clone').clone();
		clone.attr('id','task_list_'+task.rowid);
		if (TASKLIST_SHOW_DOCPREVIEW) clone.find('div[rel=docpreview]').append(JSON.parse(task.docpreview));
		clone.find('a[data-toggle="collapse"]').attr("data-target",'#task_content_'+type+'_'+task.rowid);
		clone.find('div.collapse').attr("id",'task_content_'+type+'_'+task.rowid);
		//Refresh des datas

		clone.find('[rel=taskRef]').html(task.taskRef+' '+task.taskLabel+' <span rel="progress">'+(task.progress ? task.progress+'</span>%' : ''));
		clone.find('[rel=dateo]').append(task.dateo_aff);
		clone.find('[rel=datee]').append(task.datee_aff);
		clone.find('[rel=planned_workload]').append(task.planned_workload);
		clone.find('[rel=spent_time]').append(task.spent_time);
		clone.find('[rel=calculate_progress]').append(task.calculate_progress);
		clone.find('[rel=priority]').append(task.priority);
		clone.find('[rel=description]').append(task.taskDescription);
		
		clone.find('[rel=extrafields]').append(task.extrafields);

		if(task.select_progress) {
			clone.find('[rel=select-progress]').append(task.select_progress).attr('fk-task',task.rowid).change(function() {

				var $select = $(this).find('select');
				var id_task = $(this).attr('fk-task');

				$.ajax({
					url: "ajax/interface.php",
					dataType: "json",
					crossDomain: true,
					async : true,
					data: {
						   put:'progress_task'
						   ,id : id_task
						   ,progress:$select.val()
						   ,json : 1
					}
				})
				.then(function (data){

					if(data == 'OK'){
						$("#task_list_"+id_task+" [rel=progress]").html($select.val());
					}
				});


			});

		}

		if(task.taskOF!='') clone.find('[rel=link-of]').html(task.taskOF);

		//Refresh des actions
		clone.find(".start").attr('onclick','start_task("task_list_'+task.rowid+'","'+type+'");');
		clone.find(".pause").attr('onclick','aff_popup("task_list_'+task.rowid+'","'+type+'","stop");');
		clone.find(".close").attr('onclick','aff_popup("task_list_'+task.rowid+'","'+type+'","close");');

	    if(task.tasklist_time_start!='') {
			clone.find(".pause span[rel=start-time]").html(task.tasklist_time_start);
			clone.addClass('running');
			clone.find(".pause").show();
			clone.find(".close").show();
			clone.find(".start").hide();
		}


	    /*clone.find('h3>a').on('click', function(e) {
	        e.stopPropagation();
	        e.stopImmediatePropagation();
	    }).button({ inline : true, mini: true});
	*/
		clone.appendTo('#liste_tache_'+type);
		clone.show();
	});

	if (TASKLIST_SHOW_DOCPREVIEW) initPreview();

}

function vider_liste(onglet){

	$('#liste_tache_'+onglet).empty();
}

checkLoginStatus();

function checkLoginStatus() {

	$.ajax({
		url: "ajax/interface.php",
		dataType: "html",
		crossDomain: true,
		data: {
			get:'logged-status'
		}
	})
	.then(function (data){

		if(data!='ok') {
			document.location.href = document.location.href; // reload car la session est expirée
		}
		else {
			setTimeout(function() {
				checkLoginStatus();
			}, 30000);
		}

	});

}


