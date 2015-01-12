function add_input_fields(){
            var el = document.getElementById("num_inputs");
            var i = parseInt(el.innerText) || parseInt(el.textContent);
        
            el.innerText = i + 1;
            el.textContent = i + 1;
            var container = document.getElementById("add_container");
   			var p = document.createElement("p");
        	var label = document.createElement("label");
                                                
        	label.innerText = (i + 1) + ". Answer:";
          label.textContent = (i + 1) + ". Answer:";
        	p.appendChild(label);
        	p.appendChild(document.createElement("br"));

        	var input = document.createElement("input");
            input.type = "text";
            input.name = "odpoved[" + i + "]";
            input.setAttribute("class", "regular-text");
            input.id = "odpoved_" + i;
            input.size = "35";
            input.value = "";

            p.appendChild(input);   
            var a = document.createElement("a");
            a.href = "javascript:void(0)";
            a.id = "input-_" + i;
            a.setAttribute("class", "del_input_field");
            a.setAttribute("onclick", "del_input_fields("+ i +")");
            a.innerText = '-';
            a.textContent = '-';
            p.appendChild(a); 

            container.appendChild(p);        
        }
function del_input_fields (id) {
	//alert("odpoved_"+id);
	document.getElementById("odpoved_"+id).value = "";
	document.getElementById("odpoved_"+id).parentNode.style.display='none';
	
}
