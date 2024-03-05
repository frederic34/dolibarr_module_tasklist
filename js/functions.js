$(window).resize(function () {
	resizeAll();
});

$(document).ready(function (event, ui) {
	resizeAll();
	reload_liste_tache('user');

	reload_liste_tache('workstation');

	reload_liste_of();
});

/**
 * Objet helper pour construire des tableaux sur la liste des tâches.
 */
const TasklistTableHelper = {
	// affiche un OF
	/**
	 * Retourne un objet permettant de manipuler un tableau HTML persistant
	 *
	 * @param id
	 * @param parentDiv
	 * @returns {{table: jQuery, head: jQuery, body: jQuery, reset: function, addRow: function}}
	 */
	getTable(id, parentDiv) {
		const ret = $('#' + id);
		if (ret.length) return ret.data('tasklistTableObj');
		return ({
			table: $(`<table id="${id}" class="table table-striped table-condensed product-list">`),
			head: $('<thead>'),
			body: $('<tbody>'),
			cols: [],
			init() {
				this.table.append(this.head);
				this.table.append(this.body);
				this.table.data('tasklistTableObj', this);
				parentDiv.append(this.table);
				return this;
			},
			/**
			 * Vide le <tbody> et redéfinit les colonnes et leurs en-têtes si newCols fourni.
			 * @param {object[]} newCols
			 */
			reset(newCols = undefined) {
				this.body.html('');
				this.cols.length = 0;
				this.cols.push(...newCols);
				if (newCols) {
					console.log(this.table.attr('id'));
					this.head.html('');
					const $tr = $('<tr>').appendTo(this.head);
					for (let col of this.cols) {
						console.log(col);
						const $th = $('<th>').appendTo($tr);
						$th.attr('className', `col-md-${col.width ?? '2'}`);
						$th.text(col.text);
					}
				}
			},
			/**
			 * Ajoute une ligne au tableau et la remplit avec les cellules définies par des objets.
			 * NB la fonction prend un nombre variable d'arguments, autant qu'on ne veut ajouter de cellules.
			 *
			 * @param {{}[]} cells  Objets avec un attribut html ou text (contenu affiché) et optionnellement des
			 *                      attributs className et colspan.
			 * @returns {jQuery}
			 */
			addRow(...cells) {
				console.log(this.body);
				const $tr = $('<tr>').appendTo(this.body);
				for (let cell of cells) {
					const $td = $('<td>').appendTo($tr);
					if (cell.text) $td.text(cell.text);
					else if (cell.html) $td.html(cell.html);
					if (cell.colspan) $td.attr('colspan', cell.colspan);
					if (cell.className) $td.attr('className', cell.className);
				}
				return $tr;
			}
		}).init();
	},
};

function setWorkstation(wsid) {
	$("#search_workstation").val(wsid);
	$('ul#list-workstation li,ul#list-workstation li').removeClass('active');
	$('ul#list-workstation li[ws-id=' + wsid + ']').addClass('active');

	reload_liste_tache('workstation');
}

function changeUser(fk_user) {
	$("#search_user").val(fk_user);
	$("#user-name").html($('li[user-id=' + fk_user + ']').attr('login'));
	reload_liste_tache('user');
	reload_liste_tache('workstation');
	reload_liste_of();
}

function resizeAll() {
	let doc_width = $(window).width();
	let doc_height = $(window).height();

	let nb_user;
	if (doc_width > 768) {

		nb_user = $('#select-user-list>li').length;

		if (nb_user > 10) {

			if (doc_width > 800 && nb_user > 30) {
				$('#select-user-list').width(600);
				$('#select-user-list>li').removeClass('col-md-6').addClass('col-md-4').width(160);
			} else if (doc_width > 500) {
				$('#select-user-list').width(400);
				$('#select-user-list>li').removeClass('col-md-4').addClass('col-md-6').width(160);
			}
		}

		if ($('#select-user-list').height() > doc_height) {
			$('#select-user-list').css('overflow-y', 'scroll').height(doc_height - 200);
		}

	} else {
		$('#select-user-list').css('height', null).css('overflow-y', null);
	}
}

function start_task(id_task, onglet) {
	let $li = $("#liste_tache_" + onglet + " > #" + id_task);

	$li.find('.start').hide();
	$li.find('.pause').show();
	$li.find('.close').show();

	$.ajax({
		url: "ajax/interface.php", dataType: "json", crossDomain: true, async: false, data: {
			put: 'start_task', id: id_task, json: 1
		}
	})
		.then(function (data) {
			//console.log(data);
			if (data.result == 'OK') {
				$li.find(".pause span[start-time]").html(data.tasklist_time_start);
				$li.addClass('running');
			}
		});
}

function getTimeSpent(id_task, action) {

	var res = '00:00';

	$.ajax({
		url: "ajax/interface.php", dataType: "json", crossDomain: true, async: false, data: {
			get: 'time_spent', id: id_task, action: action, json: 1
		}
	})
		.then(function (data) {
			res = data;
		});
	return res;
}

function aff_popup(id_task, onglet, action) {

	$("#confirm-add-time").modal({show: true});

	let timespent = getTimeSpent(id_task, action);
	let TTime = timespent.split(":");
	let hour = TTime[0];
	let minutes = TTime[1];
	$('#heure').val(hour);
	$('#minute').val(minutes);

	$('#valide_popup').unbind().click(function (event, ui) {

		hour = $('#heure').val();
		minutes = $('#minute').val();

		if (action == 'stop') {
			stop_task(id_task, onglet, hour, minutes);
		} else {
			close_task(id_task, onglet, hour, minutes);
		}

		$('#confirm-add-time').modal('hide');
	});

}

function stop_task(id_task, onglet, hour, minutes) {

	let $li = $('#liste_tache_' + onglet + ' > #' + id_task);
	$li.find('.start').show();
	$li.find('.pause').hide();
	$li.find('.close').show();

	$.ajax({
		url: "ajax/interface.php", dataType: "json", crossDomain: true, async: false, data: {
			put: 'stop_task',
			id: id_task,
			hour: hour,
			minutes: minutes,
			json: 1,
			id_user_selected: $('#search_user').val()
		}
	})
		.then(function (data) {
			//console.log(data);
			refresh_time_spent($li, data);
			$li.removeClass('running');
		});
}

function close_task(id_task, onglet, hour, minutes) {

	/*$("#liste_tache_"+onglet+" > #"+id_task).find('.start').hide();
	$("#liste_tache_"+onglet+" > #"+id_task).find('.pause').hide();
	$("#liste_tache_"+onglet+" > #"+id_task).find('.close').hide();*/
	$.ajax({
		url: "ajax/interface.php", dataType: "json", crossDomain: true, async: false, data: {
			put: 'close_task',
			id: id_task,
			hour: hour,
			minutes: minutes,
			json: 1,
			id_user_selected: $('#search_user').val()

		}
	})
		.then(function (data) {
			//alert(onglet);
			//reload_liste_tache(onglet);
			ajax_get_liste_task(0, 'user');
			ajax_get_liste_task(0, 'workstation');
			ajax_get_liste_task(0, 'of');
			// reload_liste_of();
			reload_liste_tache('of');
		});
}

function refresh_time_spent($obj, data) {
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
	$('ul#liste-of li[fk-of=' + fk_of + ']').addClass('active');


	_draw_of_product(fk_of);

	$('#menu-tasklist ul[role=tablist] a[href="#list-of"]').tab('show');

}

function _draw_of_product(fk_of) {

	$.ajax({
		url: 'ajax/interface.php', data: {
			get: 'task-product-of', fk_of: fk_of
		}, dataType: 'json'
	}).done(function (data) {

		$('#list-task	-of table.product-list').remove();

		var needed = false;
		var tomake = false;

		if (data.productOF.length > 0) {

			reload_tomake_needed(data);

			$('#retour-atelier').click(function () {

				var $bt = $(this);

				var originalText = $bt.text();
				$bt.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> ...");

				let TLine = [];
				$('input[rel=prod-qty-used]').closest('tr').each(function (i, item) {
					TLine.push({
						'lineid': $(item).find('input[rel=prod-qty-used]').attr('data-line-id'),
						'qty_use': $(item).find('input[rel=prod-qty-used]').val(),
						'qty_non_compliant': $(item).find('input[rel=prod-qty-non-compliant]').val()
					});
				});

				$.ajax({
					url: 'ajax/interface.php', data: {
						put: 'task-product-of', fk_of: fk_of, TLine: TLine, json: 1
					}, method: 'post', dataType: 'json'
				}).done(function (datatask) {
					console.log(datatask);

					reload_liste_tache('of', fk_of);
					_draw_of_product(fk_of);
					window.alert('Lignes modifiées');

					$bt.html(originalText);
				});
			});

		}

		if (data.productTask.length > 0) {
			for (let taskid in data.productTask) {
				const taskProducts = data.productTask[taskid];
				console.log(taskProducts);
				const $collapsedDiv = $(`#task_list_${taskid} div.collapse`);
				console.log($('#product-list-of-tomake'));
				$('#product-list-of-tomake').preprend('<table id="product-list-task-' + taskid + '" class="table table-striped table-condensed product-list"></table> ');
				const $table = $('table#product-list-task-' + taskid);
				$table.append('<tr><th>Produit</th><th>Quantité</th><th>#</th></tr>');

				for (let x in data.productTask[taskid]) {
					const line = data.productTask[taskid][x];
					$table.append('<tr><td>' + line.label + '</td><td>' + line.qty + '</td><td></td></tr>');
				}

			}
		}


	});

}

/**
 * Affichage des produits nécessaires à la fabrication des produits de l'OF et des
 * produits à fabriquer de l'OF
 *
 * @param {{conf: {}, productOF: {}[], productTask: {}}[]} data
 */
function reload_tomake_needed(data) {
	console.log('reload_tomake_needed');
	const parentDiv =$('#tables_tomake_needed');
	const tableNeeded = TasklistTableHelper.getTable('product-list-of-needed', parentDiv);
	const tableToMake = TasklistTableHelper.getTable('product-list-of-tomake', parentDiv);

	const showComplianceCols = (data.conf.global.OF_MANAGE_NON_COMPLIANT ?? false) && data.of.status.match(/^(OPEN|CLOSE)$/);
	const complianceCols = [{text: 'Conforme'}, {text: 'Non conforme'}];
	const tableNeededCols = [
		{text: 'Produit(s) nécessaire(s)', width: 8},
		{text: 'Quantité prévue'},
		{text: 'Utilisée'},
	];
	const tableToMakeCols = [
		{text: 'Produit(s) fabriqué(s)', width: 8},
		{text: 'Quantité prévue'},
		// on n'ajoute pas la dernière colonne "Fabriquée" tout de suite car dans le cas où
		// on doit gérer le non conforme, on la remplace par 2 colonnes "Conforme" et "Non conforme"
	];
	if (showComplianceCols) tableToMakeCols.push(...complianceCols);
	else tableToMakeCols.push({text: 'Fabriquée'});
	tableNeeded.reset(tableNeededCols);
	tableToMake.reset(tableToMakeCols);
	console.log(data.conf.global.OF_MANAGE_NON_COMPLIANT, data.of.status);
	console.log(tableNeeded, tableToMake);

	let needed = false;
	let tomake = false;
	let line;
	let $tr;
	for (let x in data.productOF) {
		line = data.productOF[x];
		const inputQtyUsed = `<input rel="prod-qty-used" data-line-id="${line.lineid}" value="${line.qty_used}" size="5" />`;
		const inputNonCompliant = `<input rel="prod-qty-non-compliant" data-line-id="${line.lineid}" value="${line.qty_non_compliant}" size="5" />`;
		if (line.type === 'NEEDED') {
			needed = true;
			tableNeeded.addRow(
				{text: line.label, className: 'product-label'},
				{text: line.qty, className: 'product-qty-planned'},
				{html: inputQtyUsed, className: 'product-qty-used'},
			);
		} else if (line.type === 'TO_MAKE') {
			tomake = true;
			$tr = tableToMake.addRow(
				{text: line.label, className: 'product-label'},
				{text: line.qty, className: 'product-qty-planned'},
				{html: inputQtyUsed, className: 'product-qty-used'},
			);
			if (showComplianceCols) {$(`<td>${inputNonCompliant}</td>`).appendTo($tr);}
		}
	}
	if (!needed) {
		tableNeeded.addRow({
			text: 'Aucun',
			colspan: tableNeeded.cols.length
		});
	}

	if (tomake) {
		tableToMake.addRow({
			html: '<button class="btn btn-default" id="retour-atelier">Enregistrer</button>',
			colspan: tableToMake.cols.length,
			className: 'td-enregistrer'
		});
	} else {
		tableToMake.addRow({
			text: 'Aucun',
			colspan: tableToMake.cols.length
		});
	}
}

/**
 * Onglet "Ordre de fabrication": récupération en ajax puis affichage des résultats
 */
function reload_liste_of() {

	var conf = '';
	$.ajax({
		url: "ajax/interface.php", dataType: "json", crossDomain: true, async: false, data: {
			get: 'of_liste', json: 1, fk_user: $('#search_user').val()
		}
	})
		.then(function (data) {
			//console.log(data);
			conf = data['conf'];
			let $li = $('ul#liste-of');

			$('#list-task-of table.product-list').remove();
			$li.empty();

			for (let x in data) {
				if (x == 'conf') continue;
				var OF = data[x];
				var more = '';
				if (OF.date_lancement > 0) {
					var date = new Date(OF.date_lancement * 1000);
					more = ' (' + date.getDay() + '/' + date.getMonth() + '/' + date.getFullYear() + ') ';
				}
				$li.append('<li class="list-group-item" data-rank="' + OF.rank + '" data-launching_date="' + OF.date_lancement + '" fk-of="' + OF.fk_of + '"><a href="javascript:openOF(' + OF.fk_of + ',\'' + data[x] + '\')">' + OF.label + ' [' + OF.statut + ']' + more + '</a>&nbsp;&nbsp;&nbsp;' + printShowDocumentsIcon(OF.fk_of) + '</li>');

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
											put: 'set-of-rank', json: 1, fk_of: fk_of, new_rank: (key + 1)
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
		url: "ajax/interface.php", dataType: "html", crossDomain: true, async: false, data: {
			get: 'of-documents', id: fk_of
		}
	}).done(function (data) {
		div = '<div id="doc-of-' + fk_of + '" style="display: none;">' + data + '</div>';
	});

	return '<span class="hover-cursor" onclick="showDocuments(' + fk_of + ')"><i class="fa fa-download" aria-hidden="true"></i></span>' + div;
}

function showDocuments(fk_of) {
	if (!$('#doc-of-' + fk_of).hasClass('ui-dialog-content')) $('#doc-of-' + fk_of).dialog({minWidth: 500});
	$('#doc-of-' + fk_of).dialog('open');
}

function reload_liste_tache(type, id) {

	switch (type) {

		case "user": //Utilisateurs
			if (id == null) id = $('#search_user').val();

			break;
		case "workstation": //Postes de travail
			if (id == null) id = $('#search_workstation').val();

			break;
		case "of": //Ordre de fabrication

			if (id == null) id = $('#liste-of>li.active').attr('fk-of');
			//if(id==null) id = $('#search_of').val();
			break;
	}

	ajax_get_liste_task(id, type);
}

/**
 * Récupération en ajax puis affichage de la liste de l'onglet "Tâches"
 * @param {number} id
 * @param {string} type
 */
function ajax_get_liste_task(id, type) {

	$.ajax({
		url: "ajax/interface.php", dataType: "json", crossDomain: true, async: false, data: {
			get: 'task_liste', id: id, type: type, json: 1, fk_user: $('#search_user').val()
		}
	})
		.then(function (data) {
			// console.log(data);
			refresh_liste_tache(data, type);
		});
}

/**
 * Affichage de l'onglet "Tâches"
 *
 * @param {object[]} data
 * @param {string} type
 */
function refresh_liste_tache(data, type) {
	vider_liste(type);

	$.each(data, function (i, task) {
		var clone = $('#task_list_clone').clone();
		clone.attr('id', 'task_list_' + task.rowid);
		if (TASKLIST_SHOW_DOCPREVIEW) clone.find('div[rel=docpreview]').append(JSON.parse(task.docpreview));
		clone.find('a[data-toggle="collapse"]').attr("data-target", '#task_content_' + type + '_' + task.rowid);
		clone.find('div.collapse').attr("id", 'task_content_' + type + '_' + task.rowid);
		//Refresh des datas

		clone.find('[rel=taskRef]').html(task.taskRef + ' ' + task.taskLabel + ' <span rel="progress">' + (task.progress ? task.progress + '</span>%' : ''));
		clone.find('[rel=dateo]').append(task.dateo_aff);
		clone.find('[rel=datee]').append(task.datee_aff);
		clone.find('[rel=planned_workload]').append(task.planned_workload);
		clone.find('[rel=spent_time]').append(task.spent_time);
		clone.find('[rel=calculate_progress]').append(task.calculate_progress);
		clone.find('[rel=priority]').append(task.priority);
		clone.find('[rel=description]').append(task.taskDescription);

		clone.find('[rel=extrafields]').append(task.extrafields);

		if (task.select_progress) {
			clone.find('[rel=select-progress]').append(task.select_progress).attr('fk-task', task.rowid).change(function () {

				var $select = $(this).find('select');
				var id_task = $(this).attr('fk-task');

				$.ajax({
					url: "ajax/interface.php", dataType: "json", crossDomain: true, async: true, data: {
						put: 'progress_task', id: id_task, progress: $select.val(), json: 1
					}
				})
					.then(function (data) {

						if (data == 'OK') {
							$("#task_list_" + id_task + " [rel=progress]").html($select.val());
						} else {
							if (data.error) {
								$.jnotify(TASKLIST_CONTEXT.langs['ErrorTaskNotSaved'] + ' : ' + data.error, "error");
							} else {
								$.jnotify(TASKLIST_CONTEXT.langs['ErrorTaskNotSaved'], "error");
							}
						}
					});
			});

		}

		if (task.taskOF != '') clone.find('[rel=link-of]').html(task.taskOF);

		//Refresh des actions
		clone.find(".start").attr('onclick', 'start_task("task_list_' + task.rowid + '","' + type + '");');
		clone.find(".pause").attr('onclick', 'aff_popup("task_list_' + task.rowid + '","' + type + '","stop");');
		clone.find(".close").attr('onclick', 'aff_popup("task_list_' + task.rowid + '","' + type + '","close");');

		if (task.tasklist_time_start != '') {
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
		clone.appendTo('#liste_tache_' + type);
		clone.show();
	});

	if (TASKLIST_SHOW_DOCPREVIEW) initPreview();

}

function vider_liste(onglet) {

	$('#liste_tache_' + onglet).empty();
}

checkLoginStatus();

/**
 * Envoie une requête ajax pour rafraîchir la session: si on a été déconnecté, recharge la page complète.
 */
function checkLoginStatus() {

	$.ajax({
		url: "ajax/interface.php", dataType: "html", crossDomain: true, data: {
			get: 'logged-status'
		}
	})
		.then(function (data) {

			if (data != 'ok') {
				document.location.href = document.location.href; // reload car la session est expirée
			} else {
				setTimeout(function () {
					checkLoginStatus();
				}, 30000);
			}

		});

}
