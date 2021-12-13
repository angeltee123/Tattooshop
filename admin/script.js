/*** CLIENT TABLE MANAGEMENT ***/

var workorder_change_rows = document.getElementById('change_workorder_rows');
var workorder_editing_label = document.getElementById('workorder_editing_label');
var workorder_row_fields = document.querySelectorAll(".workorder.form-control-plaintext");

var workorder_all_selected = false;
var workorder_select_all = document.getElementById('workorder_form select-all');
var workorder_row_checkboxes = document.getElementsByClassName('workorder form-check-input');

workorder_select_all.addEventListener('click', function() {
    workorder_all_selected = !workorder_all_selected;
    workorder_all_selected ? this.innerText = "Deselect" : this.innerText = "Select All";
    
    for(var i=0, count=workorder_row_checkboxes.length; i < count; i++){
    workorder_row_checkboxes[i].checked = workorder_all_selected;
    }
});

workorder_change_rows.addEventListener('click', function() {
    this.checked ? workorder_editing_label.innerText = "Stop Editing" : workorder_editing_label.innerText = "Edit";
    
    for(var i=0, count=workorder_row_fields.length; i < count; i++){
    if(this.checked) {
        workorder_row_fields[i].readOnly = false;
        workorder_row_fields[i].className = "workorder form-control";
    } else {
        workorder_row_fields[i].readOnly = true;
        workorder_row_fields[i].className = "workorder form-control-plaintext";
    }
    }
});

/*** CLIENT TABLE MANAGEMENT ***/

var client_change_rows = document.getElementById('change_client_rows');
var client_editing_label = document.getElementById('client_editing_label');
var client_row_fields = document.querySelectorAll(".client.form-control-plaintext");

var client_all_selected = false;
var client_select_all = document.getElementById('client_form select-all');
var client_row_checkboxes = document.getElementsByClassName('client form-check-input');

client_select_all.addEventListener('click', function() {
client_all_selected = !client_all_selected;
client_all_selected ? this.innerText = "Deselect" : this.innerText = "Select All";

for(var i=0, count=client_row_checkboxes.length; i < count; i++){
    client_row_checkboxes[i].checked = client_all_selected;
}
});

client_change_rows.addEventListener('click', function() {
this.checked ? client_editing_label.innerText = "Stop Editing" : client_editing_label.innerText = "Edit";

for(var i=0, count=client_row_fields.length; i < count; i++){
    if(this.checked) {
    client_row_fields[i].readOnly = false;
    client_row_fields[i].className = "client form-control";
    } else {
    client_row_fields[i].readOnly = true;
    client_row_fields[i].className = "client form-control-plaintext";
    }
}
});