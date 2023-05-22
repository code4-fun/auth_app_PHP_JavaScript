window.onload = function() {
  document.querySelector('body')?.addEventListener('click', e => {
    e.preventDefault()
    if(e.target && e.target.className === 'signin_button') {
      sign_in()
    }
  })

  document.querySelector('body')?.addEventListener('click', e => {
    e.preventDefault()
    if(e.target && e.target.className === "signout_button") {
      sign_out()
    }
  })
}

const sign_in = () => {
  const form_data = new FormData()
  form_data.append('login', document.querySelector('.form_input_login').value)
  form_data.append('password', document.querySelector('.form_input_password').value)
  fetch('/', {
    method: 'POST',
    body: form_data
  }).then(response => (
    response.json()
  )).then(response => {
    if(response.error){
      document.querySelector('.form_container').style.display = 'none'
      document.querySelector('.container')
        .insertAdjacentHTML('afterbegin', `<div class='main_container'>${response.error}</div>`)
      document.querySelector('.form_input_login').value = ''
      document.querySelector('.form_input_password').value = ''
      setTimeout(() => {
        document.querySelector('.main_container').remove()
        document.querySelector('.form_container').style.display = '';
      }, 3000)
    } else {
      document.querySelector('.form_container').remove()

      let html = `<div class='main_container'>`
      if(response.avatar){
        html += `<div class = "avatar_block">
                   <img src="${response.avatar}" alt="Avatar">
                 </div>`
      }
      html += `<div>${response.name}</div>`
      if(response.birth){
        html += `<div>Date of birth: ${response.birth}</div>`
      }
      html += `<input class="signout_button" type="submit" value="Sign out">
               </div>
               <div class="auth_success">Successful authorization</div>`

      document.querySelector('.container').insertAdjacentHTML('afterbegin', html)
      setTimeout(() => {
        document.querySelector('.auth_success').style.opacity = '0'
      }, 10000)
    }
  })
}
const sign_out = () => {
  const form_data = new FormData()
  form_data.append('exit', 'logout')
  fetch('/', {
    method: 'POST',
    body: form_data
  }).then(() => {
    document.querySelector('.main_container').remove()
    document.querySelector('.container')
      .insertAdjacentHTML('afterbegin',
        `<div class="form_container">
          <form id="form">
            <div class="form_item">
              <input class="form_input_login" type="text" placeholder="Login">
            </div>
            <div class="form_item">
              <input class="form_input_password" type="password" placeholder="Password">
            </div>
            <input class="signin_button" type="submit" value="Sign in">
          </form> 
        </div>`)
  })
}
