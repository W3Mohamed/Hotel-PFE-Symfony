// Déclarer les fonctions du chatbot dans le scope global
function toggleChatbot() {
  const chatbotWindow = document.getElementById('chatbot-window');
  const closeChatbotBtn = document.getElementById('close-chatbot-btn');
  const chatbotToggleButton = document.getElementById('chatbot-toggle-button'); // On récupère le bouton rond

  if (chatbotWindow && closeChatbotBtn && chatbotToggleButton) {
      chatbotWindow.classList.toggle('hidden');
      chatbotWindow.classList.toggle('flex');

      if (chatbotWindow.classList.contains('hidden')) {
          // Chatbot fermé
          closeChatbotBtn.classList.add('hidden');
          chatbotToggleButton.classList.remove('hidden'); // On remet le bouton rond
      } else {
          // Chatbot ouvert
          closeChatbotBtn.classList.remove('hidden');
          chatbotToggleButton.classList.add('hidden'); // On cache le bouton rond
      }
  } else {
      console.error("Élément chatbot introuvable");
  }
}

function sendQuestion(question) {
  const messagesDiv = document.getElementById('chatbot-messages');
  if (!messagesDiv) {
      console.error("Élément chatbot-messages introuvable");
      return;
  }

  // Afficher la question du client
  const clientMessage = document.createElement('div');
  clientMessage.classList.add('flex', 'justify-end', 'mb-4');
  clientMessage.innerHTML = `
      <div class="bg-[var(--noir)] text-[var(--blanc)] p-3 rounded-lg max-w-[70%]">
          ${question}
      </div>
  `;
  messagesDiv.appendChild(clientMessage);

  // Réponse automatique de l'admin
  setTimeout(() => {
      const adminMessage = document.createElement('div');
      adminMessage.classList.add('flex', 'justify-start', 'mb-4');
      adminMessage.innerHTML = `
          <div class="bg-[var(--beige)] text-[var(--blanc)] p-3 rounded-lg max-w-[70%] tapping-animation">
              ${getResponse(question)}
          </div>
      `;
      messagesDiv.appendChild(adminMessage);

      // Faire défiler vers le bas
      messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }, 1000);
}

function sendMessage() {
  const input = document.getElementById('chatbot-input');
  const messagesDiv = document.getElementById('chatbot-messages');
  
  if (!input || !messagesDiv) {
      console.error("Éléments du chatbot introuvables");
      return;
  }
  
  const message = input.value.trim();

  if (message) {
      // Afficher le message du client
      const clientMessage = document.createElement('div');
      clientMessage.classList.add('flex', 'justify-end', 'mb-4');
      clientMessage.innerHTML = `
          <div class="bg-[var(--noir)] text-[var(--blanc)] p-3 rounded-lg max-w-[70%]">
              ${message}
          </div>
      `;
      messagesDiv.appendChild(clientMessage);

      // Réponse automatique de l'admin
      setTimeout(() => {
          const adminMessage = document.createElement('div');
          adminMessage.classList.add('flex', 'justify-start', 'mb-4');
          adminMessage.innerHTML = `
              <div class="bg-[var(--beige)] text-[var(--blanc)] p-3 rounded-lg max-w-[70%] tapping-animation">
                  Merci pour votre message. Nous vous répondrons bientôt !
              </div>
          `;
          messagesDiv.appendChild(adminMessage);

          // Faire défiler vers le bas
          messagesDiv.scrollTop = messagesDiv.scrollHeight;
      }, 1000);

      // Effacer le champ de saisie
      input.value = '';
  }
}

function handleKeyPress(event) {
  if (event.key === 'Enter') {
      sendMessage();
  }
}

function getResponse(question) {
  switch (question) {
      case 'Quels sont les horaires de check-in ?':
          return 'Le check-in est à partir de 15h.';
      case 'Avez-vous des offres spéciales ?':
          return 'Oui, consultez notre page "Offres" pour plus de détails.';
      case 'Comment annuler une réservation ?':
          return 'Vous pouvez annuler via votre compte ou en nous contactant.';
      default:
          return 'Merci pour votre message. Nous vous répondrons bientôt !';
  }
}

// Événement DOMContentLoaded à la fin pour la navbar
document.addEventListener('DOMContentLoaded', function() {
  // Code pour la navbar
  const navBtns = document.querySelectorAll('.navBtn');
  const navbars = document.querySelectorAll('.navbar');

  navBtns.forEach((navBtn, index) => {
      navBtn.addEventListener('click', function () {
          navbars[index].classList.toggle('hidden'); // Affiche ou cache le menu correspondant
      });
  });
  
  // Vérifier si les éléments du chatbot existent
  const chatbotWindow = document.getElementById('chatbot-window');
  if (!chatbotWindow) {
      console.warn("L'élément chatbot-window n'existe pas dans la page");
  }
});