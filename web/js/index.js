jQuery(".story_number").click(function() {
    // Create a "hidden" input
    var aux = document.createElement("input");
    
    // Assign it the value of the specified element
    aux.setAttribute("value", this.value);
    
    // Append it to the body
    document.body.appendChild(aux);
    
    // Highlight its content
    aux.select();
    
    // Copy the highlighted text
    document.execCommand("copy");
    
    // Remove it from the body
    document.body.removeChild(aux);
    
    this.select();
});