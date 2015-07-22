<div data-role="popup" id="confirm-add-time" data-theme="a">
    <div data-role="popup" id="popupAddTime" data-theme="a" class="ui-corner-all">
        <form>
            <div style="padding:10px 20px;">
              <h3>Temps passé</h3>
              <input id="heure" value="" placeholder="H" data-theme="c" type="text" style="width: 35px;"> h 
              <input id="minute" value="" placeholder="m" data-theme="c" type="text" style="width: 35px;"> m<br><br>
              <button type="button" data-theme="b" data-icon="check" onclick="$('#confirm-add-time').popup('close');">Annuler</button> <button type="button" data-theme="b" data-icon="check" id="valide_popup">Valider</button>
            </div>
        </form>
    </div>
</div>