function toggle(){
    var desc=document.getElementById('description');
    var desc_short=document.getElementById('description_short');
    if(!desc)return true;
    if(desc.style.display=="none"){
        desc.style.display="block"
        desc_short.style.display="none"
    }
    else{
        desc.style.display="none"
        desc_short.style.display="block"
    }
    return true;
}