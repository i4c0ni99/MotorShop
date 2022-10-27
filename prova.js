const set=new Set();
const size=document.getElementById('exampleFormControlSelect1');
set.forEach(element => {
    console.log(element); 
 });

function addSize(){
    console.log(size.value);
   set.add(size.value);
   return set;
}
