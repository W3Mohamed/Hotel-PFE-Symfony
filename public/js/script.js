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

 // Stockage des données du chatbot
 let chatbotData = {
    categories: [],
    faqs: [],
    currentState: 'categories' // categories -> questions -> answer
};


// Fonction pour afficher les catégories
function displayCategories() {
    const menu = document.getElementById('chatbot-menu');
    menu.innerHTML = '<p class="font-bold mb-2">Choisissez une catégorie :</p>';
    
    const ul = document.createElement('ul');
    ul.className = 'mt-2 space-y-2';
    
    chatbotData.categories.forEach(category => {
        const li = document.createElement('li');
        li.className = 'cursor-pointer hover:underline';
        li.textContent = category.name;
        li.onclick = () => {
            loadQuestions(category.id);
            addMessageToChat(category.name, 'user');
        };
        ul.appendChild(li);
    });
    
    menu.appendChild(ul);
}

// Fonction pour charger les questions d'une catégorie
function loadQuestions(categoryId) {
    fetch(`/api/chatbot/faqs?category=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            chatbotData.faqs = data;
            displayQuestions();
        });
}

// Fonction pour afficher les questions
function displayQuestions() {
    const menu = document.getElementById('chatbot-menu');
    menu.innerHTML = '<p class="font-bold mb-2">Choisissez une question :</p>';
    
    const ul = document.createElement('ul');
    ul.className = 'mt-2 space-y-2';
    
    chatbotData.faqs.forEach(faq => {
        const li = document.createElement('li');
        li.className = 'cursor-pointer hover:underline';
        li.textContent = faq.question;
        li.onclick = () => {
            displayAnswer(faq.id);
            addMessageToChat(faq.question, 'user');
        };
        ul.appendChild(li);
    });
    
    menu.appendChild(ul);
}

// Fonction pour afficher la réponse
function displayAnswer(faqId) {
    const faq = chatbotData.faqs.find(f => f.id === faqId);
    if (faq) {
        addMessageToChat(faq.answer, 'bot');
    }
}

// Fonction pour ajouter un message au chat
function addMessageToChat(message, sender) {
    const messagesDiv = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex justify-${sender === 'user' ? 'end' : 'start'} mb-4`;
    
    const contentDiv = document.createElement('div');
    contentDiv.className = `bg-[var(--${sender === 'user' ? 'noir' : 'beige'})] text-[var(--blanc)] p-3 rounded-lg max-w-[70%] ${sender === 'bot' ? 'tapping-animation' : ''}`;
    contentDiv.textContent = message;
    
    messageDiv.appendChild(contentDiv);
    messagesDiv.appendChild(messageDiv);
    
    // Faire défiler vers le bas
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
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

  fetch('/api/chatbot/categories')
  .then(response => response.json())
  .then(data => {
      chatbotData.categories = data;
      displayCategories();
  });


});