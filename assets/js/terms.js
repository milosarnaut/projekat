function getTermStates(){
  const terms = document.querySelectorAll('.cisco-term');

  let ids = [];

  for(let i = 0; i<terms.length; i++){
    let term = terms[i];
    ids.push(term.dataset.term);
  }

  const idsJson = JSON.stringify(ids);

  const formData = new FormData();
  formData.append('ids', idsJson);

  fetch(BASE + 'api/getTermStates/', {
    method: 'POST', 
    body: formData
  })
  .then(result => result.json())
  .then(data => {
    showReservation(data);
  })
  .catch(err => console.log(err));
}

function showReservation(data){
  for(term of data.terms){
    document.querySelector('.cisco-term[data-term="'+ term.term_id +'"]').classList.remove('reserved');
    document.querySelector('.cisco-term[data-term="'+ term.term_id +'"]').classList.remove('closed');
    document.querySelector('.cisco-term[data-term="'+ term.term_id +'"]').classList.remove('open');

    if(term.status == "R"){
      document.querySelector('.cisco-term[data-term="'+ term.term_id +'"]').classList.add("reserved");
    }

    if(term.status == "Z"){
      document.querySelector('.cisco-term[data-term="'+ term.term_id +'"]').classList.add("closed");
    }
    if(term.status == "S"){
      document.querySelector('.cisco-term[data-term="'+ term.term_id +'" ]').classList.add("open");
    }
  }
}

addEventListener('load', getTermStates);