<script>
//#region USER FEEDBACK
//----------------------------------------------------------------------------------------//
//save users feedback on server.
//other users can up or downvote othes feedback
async function send_feedback(){
		const messagesElement = document.querySelector(".messages");
		const inputField = document.querySelector(".userpost-field");

		if(inputField.value == ''){
			return;
		}

		let message = {};
		message.role = '<?= htmlspecialchars($_SESSION['username']) ?>';
		message.content = inputField.value.trim();

		// const feedback_send = "../private/app/php/feedback_send.php";
		const feedback_send = "api/feedback_send"
		const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

		fetch(feedback_send, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json', // Set content type to application/json
				'X-CSRF-TOKEN': csrfToken, // Include CSRF token in the request headers
			},
			body: JSON.stringify({message: message}),
		})
		.then(response => response.json())
		.then(data => {
			//UPDATE NEW TOKEN
			document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);

			if(data.success){
				load(document.querySelector("#feedback"), 'feedback_loader.php');
				inputField.value = "";
			}
		})
		.catch(error => console.error(error));
	}

	function SubmitVote(element, action) {
		if (localStorage.getItem(element.dataset.id)) {
			return;
		}

		const pureId = element.dataset.id.replace('.json', ''); // assuming all IDs end with '.json'
		const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
		const submit_vote = "api/submit_vote";

		fetch(submit_vote, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json', // Set content type to application/json
				'X-CSRF-TOKEN': csrfToken, // Include CSRF token in the request headers
			},
			body: JSON.stringify({ id: pureId, action: action }), // Send the action and CSRF token in the request body
		})
		.then(response => {
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.json();
		})
		.then(data => {

			document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
			if(data.success){

				// Update the UI accordingly
				if (action === "upvote") {
					element.querySelector("span").textContent = data.content.up || 0; // Assuming 'data.up' contains the updated upvote count
				}
				if (action === "downvote") {
					element.querySelector("span").textContent = data.content.down || 0; // Assuming 'data.down' contains the updated downvote count
				}
			}
		})
		.catch(error => {
			console.error('Fetch error:', error);
		});
		localStorage.setItem(element.dataset.id, "true"); 

		voteHover();
	}

	async function voteHover(){
		let messages = document.querySelectorAll(".message");

		messages.forEach((message)=>{
			let voteButtons = message.querySelectorAll(".vote")

			voteButtons.forEach((voteButton)=>{
				if(localStorage.getItem(voteButton.dataset.id)){
					voteButton.classList.remove("vote-hover");
				}else{
					voteButton.classList.add("vote-hover");
				}
			})
		})
	}
	//#endregion
</script>
