
$( "#list-task-user" ).on( "pagecreate", function( event, ui ) {
	reload_liste_tache('user');
	$("#search_user").on( "change", function(event, ui) {
 		reload_liste_tache('user');
 		reload_liste_tache('workstation');
 		reload_liste_of();
 		//reload_liste_tache('of');
	});
} );

$( "#list-task-workstation" ).on( "pagecreate", function( event, ui ) {
	reload_liste_tache('workstation');
	$("#search_workstation").on( "change", function(event, ui) {
 		reload_liste_tache('workstation');
	});
} );

$( "#list-task-of" ).on( "pagecreate", function( event, ui ) {
	reload_liste_of();
	
} );



function start_task(id_task,onglet){

	$("#liste_tache_"+onglet+" > #"+id_task).find('.start').hide();
	$("#liste_tache_"+onglet+" > #"+id_task).find('.pause').show();
	$("#liste_tache_"+onglet+" > #"+id_task).find('.close').show();
	
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
		if(data == 1){
			
		}
	});
}

function getTimeSpent(id_task){
	
	var res = '00:00';
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   get:'time_spent'
			   ,id : id_task
			   ,json : 1
		}
	})
	.then(function (data){
		res = data;
	});
	
	return res;
}

function aff_popup(id_task,onglet,action){
	$('#confirm-add-time').panel('open');
	
	timespent = getTimeSpent(id_task);
	console.log(timespent);
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
		
		$('#confirm-add-time').panel('close');
	});
	
}

function stop_task(id_task,onglet,hour,minutes){
	
	$("#liste_tache_"+onglet+" > #"+id_task).find('.start').show();
	$("#liste_tache_"+onglet+" > #"+id_task).find('.pause').hide();
	$("#liste_tache_"+onglet+" > #"+id_task).find('.close').show();
	
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
		refresh_time_spent($('#liste_tache_'+onglet+' > #'+id_task).find('span[rel=spent_time]'), time);
		//refresh_liste_tache(data,type);
	});
}

function close_task(id_task,onglet){
	
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
		//console.log(data);
		//alert(onglet);
		reload_liste_tache(onglet);
	});
}

function refresh_time_spent(obj, time)
{
	$(obj).text(time);
}

function openOF(fk_of, numero) {
	
	//reload_liste_tache('of', fk_of);
	reload_liste_of();
	
	/*inutile déjà fait par le framework
	 * var currentPage = $(':mobile-pagecontainer').pagecontainer( "getActivePage" ).attr('id');
	if(currentPage!='list-task-of')
	 */
	
	$('#list-task-of div[data-role="header"] h1').html("Tâches OF "+numero);
	
	_draw_of_product(fk_of);
	
	$.mobile.changePage('#list-task-of');
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
		
		$('#list-task-of div.ui-content table.product-list').remove();
		
		if(data.productOF.length>0) {
			
			$('#list-task-of div#liste_tache_of').before('<table data-role="table" id="product-list-of" class="ui-responsive table-stroke product-list"></table> ');
			
			$table = $('#list-task-of div.ui-content table#product-list-of');
			
			$table.append('<tr><th>Produit</th><th>Quantité</th><th>#</th></tr>');
			
			for(x in data.productOF) {
				
				line = data.productOF[x]; 
				
				$table.append('<tr><td>'+line.label+'</td><td>'+line.qty+'</td><td></td></tr>');	
			}
			
			$table.table({
			  defaults: true
			});
			
			
			
			
			
		}
		
		if(data.productTask.length>0) {
			for(taskid in data.productTask) {
				$('#task_list_'+taskid+' .ui-collapsible-content').preprend('<table data-role="table" id="product-list-task-'+taskid+'" class="ui-responsive table-stroke product-list"></table> ');
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

function selectUser(fk_user) {
	$('#search_user').val(fk_user);
	$('#search_user').change();
	//$('#search_user').selectmenu('refresh');
	$( "#select-user" ).panel( "close" );
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
			
			$li.append('<li><a href="javascript:openOF('+x+',\''+data[x]+'\')">'+data[x]+'</a></li>');
			
		}
	
		$li.listview();
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

	//$( '#liste_tache_'+onglet ).collapsibleset( "destroy" );

	$.each(data,function(i,task){
		var clone = $('#task_list_clone').clone();
		clone.attr('id','task_list_'+task.rowid);
		
		//Refresh des datas
		clone.find('[rel=taskRef]').html(task.taskRef+' '+task.taskLabel);
		clone.find('[rel=taskRef] a[data-role=button]').button();
		clone.find('[rel=dateo]').append(task.dateo_aff);
		clone.find('[rel=datee]').append(task.datee_aff);
		clone.find('[rel=planned_workload]').append(task.planned_workload);
		clone.find('[rel=spent_time]').append(task.spent_time);
		clone.find('[rel=progress]').append(task.progress);
		clone.find('[rel=priority]').append(task.priority);
		
		//Refresh des actions
		clone.find(".start").attr('onclick','start_task("task_list_'+task.rowid+'","'+type+'");');
		clone.find(".pause").attr('onclick','aff_popup("task_list_'+task.rowid+'","'+type+'","stop");');
		clone.find(".close").attr('onclick','aff_popup("task_list_'+task.rowid+'","'+type+'","close");');
	    clone.find('h3>a').on('click', function(e) {
	        e.stopPropagation();
	        e.stopImmediatePropagation();          
	    }).button({ inline : true, mini: true});    
	
		clone.appendTo('#liste_tache_'+type);
		clone.show();
		clone.collapsible();
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
