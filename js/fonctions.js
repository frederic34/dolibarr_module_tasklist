//Vartiables globales
var id_ticket_attente = '';
var id_ba_attente = '';

$(window).load(function(){
	switch_onglet('onglet1');
});

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
	
	TTask = ajax_get_liste_task(id,type);
	//console.log(TTask);
	refresh_liste_tache(TTask);
}

function ajax_get_liste_task(id,type){
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "html",
		crossDomain: true,
		data: {
			   get:'task_liste'
			   ,id : id
			   ,type : type
		}
	})
	.then(function (data){
		return data;
	});
}

function refresh_liste_tache(TTask){
	
	vider_liste();
}

function vider_liste(){
	
}
