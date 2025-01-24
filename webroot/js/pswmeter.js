/**
 * PSWMeter
 * @author pascualmj
 * @see https://github.com/pascualmj/pswmeter
 */

/**
 *
 * @param opts
 * @returns {{getScore: (function(): number), containerElement: HTMLElement}}
 */
function passwordStrengthMeter(opts) {

	// Add styles inside body
	const customStyles = document.createElement('style')
	document.body.prepend(customStyles)
	customStyles.innerHTML = `
		${opts.containerElement} {
			height: ${opts.height || 4}px;
			background-color: #eee;
			position: relative;
			overflow: hidden;
			border-radius: ${opts.borderRadius ? opt.borderRadius.toString() : 2}px;
		}
    ${opts.containerElement} .password-strength-meter-score {
      height: inherit;
      width: 0%;
      transition: .3s ease-in-out;
      background: ${opts.colorScore1 || '#ff0000'};
    }
    ${opts.containerElement} .password-strength-meter-score.psms-25 {width: 25%; background: ${opts.colorScore1 || '#ff0000'};}
    ${opts.containerElement} .password-strength-meter-score.psms-50 {width: 50%; background: ${opts.colorScore2 || '#fff400'};}
    ${opts.containerElement} .password-strength-meter-score.psms-75 {width: 75%; background: ${opts.colorScore3 || '#a3ff00'};}
    ${opts.containerElement} .password-strength-meter-score.psms-100 {width: 100%; background: ${opts.colorScore4 || '#2cba00'};}`

	// Container Element
	const containerElement = document.getElementById(opts.containerElement.slice(1))
	containerElement.classList.add('password-strength-meter')

	// Score Bar
	let scoreBar = document.createElement('div')
	scoreBar.classList.add('password-strength-meter-score')

	// Append score bar to container element
	containerElement.appendChild(scoreBar)

	// Password input
	const passwordInput = document.getElementById(opts.passwordInput.slice(1))
	let passwordInputValue = ''
	passwordInput.addEventListener('keyup', function() {
		passwordInputValue = this.value
		checkPassword()
	})

	// Chosen Min Length
	let pswMinLength = opts.pswMinLength || 8

	// Score Message
	let scoreMessage = opts.showMessage ? document.getElementById(opts.messageContainer.slice(1)) : null
	let messagesList = opts.messagesList === undefined ? ['Empty password', 'Too simple', 'Simple', 'That\'s OK', 'Great password!'] : opts.messagesList
	if (scoreMessage) { scoreMessage.textContent = messagesList[0] || 'Empty password'}

	// Check Password Function
	function checkPassword() {

        let score = getScore()
        updateScore(score)

	}

	// Get Score Function
	function getScore() {

		let score = 0

        let regexLower = new RegExp('(?=.*[a-z])')
        let regexUpper = new RegExp('(?=.*[A-Z])')
        let regexDigits = new RegExp('(?=.*[0-9])')
        let regexSymbols = new RegExp('(?=.*[^a-zA-Z\d\s])')
        // For length score print user selection or default value
        let regexLength = new RegExp('(?=.{' + pswMinLength + ',})')

        if (passwordInputValue.match(regexLower) && passwordInputValue.match(regexUpper)) { ++score }
        if (passwordInputValue.match(regexSymbols)) { ++score }
        if (passwordInputValue.match(regexDigits)) { ++score }
        if (passwordInputValue.match(regexLength)) { ++score }
        if (score === 0 && passwordInputValue.length > 0) { ++score }

        return score
	}

	// Show Score Function
	function updateScore(score) {
        switch(score) {
          case 1:
            scoreBar.className = 'password-strength-meter-score psms-25'
            if (scoreMessage) { scoreMessage.textContent = messagesList[1] || 'Too simple' }
            containerElement.dispatchEvent(new Event('onScore1', { bubbles: true }))
            break
          case 2:
            scoreBar.className = 'password-strength-meter-score psms-50'
            if (scoreMessage) { scoreMessage.textContent = messagesList[2] || 'Simple' }
            containerElement.dispatchEvent(new Event('onScore2', { bubbles: true }))
            break
          case 3:
            scoreBar.className = 'password-strength-meter-score psms-75'
            if (scoreMessage) { scoreMessage.textContent = messagesList[3] || 'That\'s OK' }
            containerElement.dispatchEvent(new Event('onScore3', { bubbles: true }))
            break
          case 4:
            scoreBar.className = 'password-strength-meter-score psms-100'
            if (scoreMessage) { scoreMessage.textContent = messagesList[4] || 'Great password!' }
            containerElement.dispatchEvent(new Event('onScore4', { bubbles: true }))
            break
          default:
            scoreBar.className = 'password-strength-meter-score'
            if (scoreMessage) { scoreMessage.textContent = messagesList[0] || 'No data' }
            containerElement.dispatchEvent(new Event('onScore0', { bubbles: true }))
        }
  }

  // Return anonymous object with properties
  return {
  	containerElement,
  	getScore
  }

}
window.addEventListener("load",init);
function init() {
    // Run pswmeter with options
    const myPassMeter = passwordStrengthMeter({
        containerElement: '#pswmeter',
        passwordInput: '#new-password',
        showMessage: showMessage,
        messageContainer: '#pswmeter-message',
        messagesList: messagesList,
        pswMinLength: pswMinLength,
    });
    for (let i = 0; i <= 4; i++) {
        myPassMeter.containerElement.addEventListener('onScore' + i, function() {
            document.getElementById("btn-submit").disabled = i < requiredScore;
        })
    }

    document.getElementById("new-password").dispatchEvent(new Event("keyup"));
    if (myPassMeter.getScore < requiredScore) {
        document.getElementById("btn-submit").disabled = true;
    }

}
