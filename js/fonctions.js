
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
	.then(function (time){
		//console.log(data);
		refresh_time_spent($li.find('span[rel=spent_time]'), time);
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
		reload_liste_tache(onglet);
	});
}

function refresh_time_spent(obj, time)
{
	$(obj).text(time);
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
			
			$table = $('<table id="product-list-of" class="table table-striped table-condensed product-list"></table> ');
			
			$table.append('<thead><tr><th class="col-md-8">Produit Nécessaire</th><th class="col-md-2">Quantité prévue</th><th class="col-md-2">Utilisée</th></tr></thead><tbody></tbody>');
			
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
			
			$table2.append('<thead><tr><th class="col-md-8">Produit Fabriqué</th><th class="col-md-2">Quantité prévue</th><th class="col-md-2">Fabriquée</th></tr></thead><tbody></tbody>');
			
			for(x in data.productOF) {
				
				line = data.productOF[x]; 
				
				if(line.type == 'TO_MAKE'){
					$tr = $('<tr />');
					$tr.append('<td>'+line.label+'</td>');
					$tr.append('<td>'+line.qty+'</td>');
					
					$tr.append('<td><input rel="prod-qty-used" line-id="'+line.lineid+'" type="text" value="'+line.qty_used+'" size="5" /></td>');
					$table2.find('tbody').append($tr);
					
					tomake = true;
				}
			}
			
			if(needed || tomake) {
				$table2.append('<tr><td align="right" colspan="3"><button class="btn btn-default" id="retour-atelier">Enregistrer</button></td></tr>');	
				$('#list-task-of div#liste_tache_of').before($table);
				$('#list-task-of div#liste_tache_of').before($table2);

			}
			
			$('#retour-atelier').click(function() {
				
				var $bt = $(this); 
				
				var originalText = $bt.text();
				$bt.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> ...");
				
				TLine=[];
				$('input[rel=prod-qty-used]').each(function(i,item) {
					TLine.push({
						'lineid':$(item).attr('line-id')
						,'qty_use':$(item).val()
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
				}).done(function(data){
					console.log(data);
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

function reload_liste_of() {
	
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
	
		$li = $('ul#liste-of');
		
		$li.empty();
		for(x in data) {
			
			$li.append('<li class="list-group-item" fk-of="'+x+'"><a href="javascript:openOF('+x+',\''+data[x]+'\')">'+data[x]+'</a></li>');
			
		}

	});
	
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
			if(id==null) id = $('#search_of').val();
			
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
		clone.find('a[data-toggle="collapse"]').attr("data-target",'#task_content_'+type+'_'+task.rowid);
		clone.find('div.collapse').attr("id",'task_content_'+type+'_'+task.rowid);
		//Refresh des datas
		clone.find('[rel=taskRef]').html(task.taskRef+' '+task.taskLabel);
		clone.find('[rel=dateo]').append(task.dateo_aff);
		clone.find('[rel=datee]').append(task.datee_aff);
		clone.find('[rel=planned_workload]').append(task.planned_workload);
		clone.find('[rel=spent_time]').append(task.spent_time);
		clone.find('[rel=progress]').append(task.progress);
		clone.find('[rel=priority]').append(task.priority);
		
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
