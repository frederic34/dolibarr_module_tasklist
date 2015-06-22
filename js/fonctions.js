//Vartiables globales
var id_ticket_attente = '';
var id_ba_attente = '';

$(document).ready(function(){
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
	
	$("#confirm-add-time" ).popup();
	
});

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
	
	var res = 0;
	
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
	
	$("#confirm-add-time" ).popup('open');
	timespent = getTimeSpent(id_task);
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
		
		$("#confirm-add-time").popup('close');
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
		}
	})
	.then(function (data){
		//console.log(data);
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
		}
	})
	.then(function (data){
		//console.log(data);
		//alert(onglet);
		reload_liste_tache(onglet);
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

function reload_liste_tache(onglet, id){
	
	switch(onglet){
		
		case "onglet1": //Utilisateurs
			if(id==null) id = $('#search_user option:selected').val();
			type = 'user';
			break;
		case "onglet2": //Postes de travail
			if(id==null) id = $('#search_workstation option:selected').val();
			type = 'workstation';
			break;
		case "onglet3": //Ordre de fabrication
			if(id==null) id = $('#search_of option:selected').val();
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
		clone.find(".start").attr('onclick','start_task("task_list_'+task.rowid+'","'+onglet+'");');
		clone.find(".pause").attr('onclick','aff_popup("task_list_'+task.rowid+'","'+onglet+'","stop");');
		clone.find(".close").attr('onclick','aff_popup("task_list_'+task.rowid+'","'+onglet+'","close");');
		
		clone.appendTo('#liste_tache_'+onglet);
		
		clone.show();
	});

}

function vider_liste(onglet){
	
	$('#liste_tache_'+onglet).empty();
}
