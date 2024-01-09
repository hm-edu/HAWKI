<?php
session_start();

if (!isset($_SESSION['username'])) {
	header("Location: login.php");
	exit;
}

?>
<head>
	<title>HM-KI</title>
	<?php 
  if (file_exists(".env")){
	$env = parse_ini_file('.env');
  }
  $favicon = isset($env) ? ($env["FAVICON_URI"]??false) : getenv("FAVICON_URI");
  if ($favicon) {
	echo 
	'<link rel="shortcut icon" type="image/x-icon" href="'. $favicon .'" media="screen" />
	<link rel="icon" type="image/x-icon" href="'. $favicon .'" media="screen" />
	';
  }
 ?>
 </head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="/highlightjs/styles/vs.css">
<script src="/highlightjs/highlight.min.js"></script>

<link rel="stylesheet" href="app.css">

<div class="wrapper">
  <div class="sidebar">
	<div class="logo" onclick="load(this, 'chat.htm')">
	 <img src=<?php 
	 	$logo = isset($env) ? ($env["LOGO"]??false) : getenv("LOGO");
		if ($logo) {
			echo $logo;
		}
	 ?> alt="HAWK Logo" width="150px">
	</div>
	<div class="menu">

		<form>
			<details>
				<summary>
					<h3>Modell ⓘ</h3>
				</summary>
				Wähle eines der KI Modelle.
			</details>
			<select id = "GPT-Version">
				<option value="gpt-4-1106-preview" token_limit="128000" selected="selected"> GPT 4 Turbo </option>
				<option value="gpt-4" token_limit="8192"> GPT 4</option>
				<option value="gpt-3.5-turbo" token_limit="4096"> GPT 3.5 Turbo </option>
			</select>
			<p id="GPT4-Hinweis", style="color: crimson"> GPT 4 behauptet GPT 3 zu sein.</p>
		</form>

		<details>
			<summary>
				<h3>Konversation ⓘ</h3>
			</summary>
			Ein Chatbereich wie bei ChatGPT, für einen schnellen Einstieg in jede beliebige Aufgabe.
		</details>
	  <div class="menu-item" onclick="load(this, 'chat.htm')">
		<svg viewBox="0 0 24 24"><path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2M20 16H5.2L4 17.2V4H20V16Z" /></svg>
		Chat
	  </div>
	  
	  <details>
		  <summary>
			  <h3>Virtuelles Büro ⓘ</h3>
		  </summary>
		 Gespräche mit fiktiven Expert*innen, um sich in fachfremden Bereichen einzuarbeiten und gezieltere Anfragen an echte Hochschul-Expert*innen zu stellen.
	  </details>
	  <div class="menu-item" onclick="submenu(this)">
		<svg viewBox="0 0 24 24"><path d="M13.07 10.41A5 5 0 0 0 13.07 4.59A3.39 3.39 0 0 1 15 4A3.5 3.5 0 0 1 15 11A3.39 3.39 0 0 1 13.07 10.41M5.5 7.5A3.5 3.5 0 1 1 9 11A3.5 3.5 0 0 1 5.5 7.5M7.5 7.5A1.5 1.5 0 1 0 9 6A1.5 1.5 0 0 0 7.5 7.5M16 17V19H2V17S2 13 9 13 16 17 16 17M14 17C13.86 16.22 12.67 15 9 15S4.07 16.31 4 17M15.95 13A5.32 5.32 0 0 1 18 17V19H22V17S22 13.37 15.94 13Z" /></svg>
		Team
	  </div>
	  <div class="submenu">
			<div class="submenu-item" onclick="load(this, 'finance.htm')">Finanzen</div>
			<div class="submenu-item" onclick="load(this, 'science.htm')">Forschung</div>
			<div class="submenu-item" onclick="load(this, 'marketing.htm')">Marketing</div>
			<div class="submenu-item" onclick="load(this, 'programming.htm')">Programmierung</div>
			<div class="submenu-item" onclick="load(this, 'law.htm')">Rechtsberatung</div>
			<div class="submenu-item" onclick="load(this, 'socialmedia.htm')">Social Media</div>
		</div>
		
		<details>
			  <summary>
				  <h3>Lernraum ⓘ</h3>
			  </summary>
			  Die Lernräume sollen helfen, die verschiedenen Unterstützungsmöglichkeiten zu verstehen und zu lernen, was einen effektiven Prompt ausmacht.
		  </details>
	  <div class="menu-item" onclick="submenu(this)">
		<svg viewBox="0 0 24 24"><path d="M14.6,16.6L19.2,12L14.6,7.4L16,6L22,12L16,18L14.6,16.6M9.4,16.6L4.8,12L9.4,7.4L8,6L2,12L8,18L9.4,16.6Z" /></svg>
		Wiss. Arbeiten
	  </div>
	  <div class="submenu">
		  <div class="submenu-item" onclick="load(this, 'datascience.htm')">Datenanalyse</div>
		  <div class="submenu-item" onclick="load(this, 'feedback.htm')">Feedback</div>
		  <div class="submenu-item" onclick="load(this, 'methodologie.htm')">Methodologie</div>
		  <div class="submenu-item" onclick="load(this, 'literature.htm')">Literaturrecherche</div>
		  <div class="submenu-item" onclick="load(this, 'research.htm')">Rechercheunterstützung</div>
		  <div class="submenu-item" onclick="load(this, 'writing.htm')">Schreibhilfe</div>
	  </div>
	  
	  <div class="menu-item" onclick="submenu(this)">
		<svg viewBox="0 0 24 24"><path d="M6,3A1,1 0 0,1 7,4V4.88C8.06,4.44 9.5,4 11,4C14,4 14,6 16,6C19,6 20,4 20,4V12C20,12 19,14 16,14C13,14 13,12 11,12C8,12 7,14 7,14V21H5V4A1,1 0 0,1 6,3M7,7.25V11.5C7,11.5 9,10 11,10C13,10 14,12 16,12C18,12 18,11 18,11V7.5C18,7.5 17,8 16,8C14,8 13,6 11,6C9,6 7,7.25 7,7.25Z" /></svg>
		Organisation
	  </div>
	  <div class="submenu">
			<div class="submenu-item" onclick="load(this, 'eventmanagement.htm')">Eventmanagement</div>
			<div class="submenu-item" onclick="load(this, 'learning.htm')">Lernstrategien</div>
			<div class="submenu-item" onclick="load(this, 'motivation.htm')">Motivation</div>
			<div class="submenu-item" onclick="load(this, 'stressmanagement.htm')">Stressmanagement</div>
			<div class="submenu-item" onclick="load(this, 'tables.htm')">Tabellen</div>
			<div class="submenu-item" onclick="load(this, 'timemanagement.htm')">Zeitmanagement</div>
		</div>
		
	  <div class="menu-item" onclick="submenu(this)">
		<svg viewBox="0 0 24 24"><path d="M15.54,3.5L20.5,8.47L19.07,9.88L14.12,4.93L15.54,3.5M3.5,19.78L10,13.31C9.9,13 9.97,12.61 10.23,12.35C10.62,11.96 11.26,11.96 11.65,12.35C12.04,12.75 12.04,13.38 11.65,13.77C11.39,14.03 11,14.1 10.69,14L4.22,20.5L14.83,16.95L18.36,10.59L13.42,5.64L7.05,9.17L3.5,19.78Z" /></svg>
		Kreativität
	  </div>
	  <div class="submenu">
			<div class="submenu-item" onclick="load(this, 'copywriting.htm')">Copywriting</div>
			<div class="submenu-item" onclick="load(this, 'designthinking.htm')">Design Thinking</div>
			<div class="submenu-item" onclick="load(this, 'gamification.htm')">Gamification</div>
			<div class="submenu-item" onclick="load(this, 'ideageneration.htm')">Ideenfindung</div>
			<div class="submenu-item" onclick="load(this, 'interview.htm')">Interviewfragen</div>
			<div class="submenu-item" onclick="load(this, 'prototyping.htm')">Prototyping</div>
		</div>
		
	</div>
	<div class="info">
	  <a href="#" onclick="load(this, 'about.htm')">Über HM-KI</a>
	  <!-- <a href="#" id="feedback" onclick="load(this, 'userpost.php')">Feedback</a> -->
	  <a href=<?php 
	  	if (file_exists(".env")){
			$env = parse_ini_file('.env');
		}
		if ((isset($env) ? $env["Authentication"] : getenv("Authentication")) == "OIDC"){
		echo "oidc_logout.php";
		} else echo "logout.php" ?>>Abmelden (<?php echo $_SESSION['username']?>)</a>
	  <br>
	  <a href="/datenschutz" target="_blank">Datenschutz</a>
	  <a href="/impressum" target="_blank">Impressum</a>
	</div>
  </div>
  
  <div class="main">
	  <div></div>
	<div class="messages">
	
	 
	 <div class="limitations">
		 <div>
			 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			 <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#06B044" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			 <path d="M22 4L12 14.01L9 11.01" stroke="#06B044" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			 </svg>
			 <h2>Möglichkeiten</h2>
			 <div>
				 <div class="limitation-item">
					 <strong>Kontextverständnis</strong> - Merkt sich, was vorab in der Konversation gesagt wurde.
				 </div>
				 <div class="limitation-item">
					 <strong>Iteration</strong> - Erlaubt nachträgliche Korrekturen generierter Ergebnisse.
				 </div>
				 <div class="limitation-item">
					 <strong>Formatierung</strong> - Gibt generierte Ergebnisse in gewünschter Form aus.
				 </div>
			  </div>
		 </div>
		 <div>
			 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			 <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#FF5C00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			 <path d="M12 8V12" stroke="#FF5C00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			 <path d="M12 16H12.01" stroke="#FF5C00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			 </svg>
			 <h2>Limitationen</h2>
			 <div>
				<div class="limitation-item">
					<strong>Unvollständig</strong> - Generiert gelegentlich falsche Informationen.
				</div>
				<div class="limitation-item">
					<strong>Vorsicht</strong> - Generiert gelegentlich gefährdende oder voreingenommene Informationen.
				</div>
				<div class="limitation-item">
					<strong>Limitierung</strong> - Das Sprachmodell greift ausschließlich auf Wissen bis zum Jahr 2021 zu.
				</div>
			 </div>
		 </div>
	 </div>
	 
	 
	<div class="message me" data-role="system">
		<div class="message-content">
			<div class="message-icon">System</div>
			<div class="message-text">You are a helpful assistant who works at the University of Applied Sciences in Munich.</div>
		</div>
	</div>
	  

	</div>
	
	<div class="input-container">
		<div class="input">
			<textarea class="input-field" type="text" placeholder="Hier kannst Du deine Anfrage stellen" oninput="resize(this)" onkeypress="handleKeydown(event)"></textarea>
			<div class="input-send" onclick="request()">
				<svg viewBox="0 0 24 24">
					<path d="M3 20V4L22 12M5 17L16.85 12L5 7V10.5L11 12L5 13.5M5 17V7 13.5Z" />
				</svg>
			</div>
		</div>
		<div class="betaMessage">
			Betaversion - befindet sich noch in Entwicklung
		</div>
	</div>
	  
  </div>
  
  <template id="message">
		 <div class="message">
			 <div class="message-content">
			   <div class="message-icon">
				   KI
			   </div>
			   <div class="message-text">
				   Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quos incidunt, quidem soluta excepturi, ullam enim tempora.
			   </div>
			 </div>
		   </div>
	 </template>
</div>

<div class="modal" onclick="modalClick(this)" id="data-protection"> 
	<div class="modal-content">
		<h2>Nutzungshinweis</h2>
		<p>HM-KI verwendet die API von OpenAI. Das bedeutet, dass die eingegeben Informationen an OpenAI übermittelt werden. Wir bitten daher keine Daten mit Personenbezug, also Angaben, die auf eine Person unmittelbar oder zumindest mittelbar abstellen sowie Inhalte die durch das <a href="https://dejure.org/gesetze/UrhG"> Urhebergesetz</a> geschützt sind einzugeben, um Verstöße gegen geltendes Recht und die Nutzungsbedingungen von <a href="https://openai.com/policies/terms-of-use"> OpenAI </a> zu vermeiden. </p>
		<button>Bestätigen</button>
	</div>
</div>

<script src="interface.js"></script>
