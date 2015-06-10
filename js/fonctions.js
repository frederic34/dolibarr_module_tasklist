//Vartiables globales
var id_ticket_attente = '';
var id_ba_attente = '';

$(window).load(function(){
	switch_onglet('onglet1');
	
	reload_liste_tache('onglet1');
	
	/*
	 * ACTION LISTE DEROULANTE
	 */
	$("#search_user").on( "change", function(event, ui) {
 		reload_liste_tache('onglet1');
	});
	
	$("#search_workstation").on( "change", function(event, ui) {
 		reload_liste_tache('onglet2');
	});
	
	$("#search_of").on( "change", function(event, ui) {
 		reload_liste_tache('onglet3');
	});
	
	/*
	 * ACTIONS BOUTONS
	 */
	$(".start").on( "click", function(event, ui) {
		id_task = $(this).closest('div[id^="task_list_"]').attr('id');
 		start_task(id_task);
	});
	
	$(".pause").on( "click", function(event, ui) {
		id_task = $(this).closest('div[id^="task_list_"]').attr('id');
 		stop_task(id_task);
	});
	
	$(".close").on( "click", function(event, ui) {
		id_task = $(this).closest('div[id^="task_list_"]').attr('id');
 		close_task(id_task);
	});
	
	
	
});

function start_task(id_task){
	
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
		refresh_liste_tache(data,type);
	});
}

function stop_task(id_task){
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   put:'stop_task'
			   ,id : id_task
			   ,json : 1
		}
	})
	.then(function (data){
		//console.log(data);
		refresh_liste_tache(data,type);
	});
}

function close_task(id_task){
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   put:'close_task'
			   ,id : id_task
			   ,json : 1
		}
	})
	.then(function (data){
		//console.log(data);
		refresh_liste_tache(data,type);
	});
}

// Actions graphique au changement d'onglet
function switch_onglet(onglet){
	
	$("#onglet1,#onglet2,#onglet3,#onglet4,#onglet5").removeClass("ui-btn-active");
	$("#"+onglet).addClass("ui-btn-active");	
	
	switch(onglet)
	{
		case "onglet1": //Utilisateurs
			$("#corps-2, #corps-3").hide();
			$("#corps-1").show();
			break;
		case "onglet2": //Postes de travail
			$("#corps-1, #corps-3").hide();
			$("#corps-2").show();
			break;
		case "onglet3": //Ordre de fabrication
			$("#corps-1, #corps-2").hide();
			$("#corps-3").show();
			break;
	}
	
	reload_liste_tache(onglet);
}

function reload_liste_tache(onglet){
	
	switch(onglet){
		
		case "onglet1": //Utilisateurs
			id = $('#search_user option:selected').val();
			type = 'user';
			break;
		case "onglet2": //Postes de travail
			id = $('#search_workstation option:selected').val();
			type = 'workstation';
			break;
		case "onglet3": //Ordre de fabrication
			id = $('#search_of option:selected').val();
			type = 'of';
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
		}
	})
	.then(function (data){
		//console.log(data);
		refresh_liste_tache(data,type);
	});
}

function refresh_liste_tache(data,onglet){
	
	vider_liste(onglet);

	$.each(data,function(i,task){
		clone = $('#task_list_clone').clone();
		
		clone.attr('id','task_list_'+task.rowid);
		
		clone.find('[rel=taskRef]').text(task.taskRef);
		clone.find('[rel=dateo]').append(task.dateo);
		clone.find('[rel=datee]').append(task.datee);
		clone.find('[rel=planned_workload]').append(task.planned_workload);
		clone.find('[rel=spent_time]').append(task.spent_time);
		clone.find('[rel=progress]').append(task.progress);
		clone.find('[rel=priority]').append(task.priority);

		clone.appendTo('#liste_tache_'+onglet);
		
		clone.show();
	});

}

function vider_liste(onglet){
	
	$('#liste_tache_'+onglet).empty();
}
