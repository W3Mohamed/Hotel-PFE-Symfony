// Déclarer les fonctions du chatbot dans le scope global
function toggleChatbot() {
    const chatbotWindow = document.getElementById('chatbot-window');
    const closeChatbotBtn = document.getElementById('close-chatbot-btn');
    const chatbotToggleButton = document.getElementById('chatbot-toggle-button');

    if (chatbotWindow && closeChatbotBtn && chatbotToggleButton) {
        chatbotWindow.classList.toggle('hidden');
        chatbotWindow.classList.toggle('flex');

        if (chatbotWindow.classList.contains('hidden')) {
            closeChatbotBtn.classList.add('hidden');
            chatbotToggleButton.classList.remove('hidden');
        } else {
            closeChatbotBtn.classList.remove('hidden');
            chatbotToggleButton.classList.add('hidden');
            showOptions();
        }
    }
}

function showOptions() {
    const optionsDiv = document.getElementById('chatbot-options');
    const menuContainer = document.getElementById('chatbot-menu-container');
    
    optionsDiv.classList.remove('hidden');
    menuContainer.classList.add('hidden');
    
    // Bouton pour afficher les catégories
    document.getElementById('show-categories-btn').onclick = function() {
        menuContainer.classList.toggle('hidden');
        if (!menuContainer.classList.contains('hidden')) {
            displayCategories();
        }
    };
}

// function sendMessage() {
//   const input = document.getElementById('chatbot-input');
//   const messagesDiv = document.getElementById('chatbot-messages');
  
//   if (!input || !messagesDiv) {
//       console.error("Éléments du chatbot introuvables");
//       return;
//   }
  
//   const message = input.value.trim();

//   if (message) {
//       // Afficher le message du client
//       const clientMessage = document.createElement('div');
//       clientMessage.classList.add('flex', 'justify-end', 'mb-4');
//       clientMessage.innerHTML = `
//           <div class="bg-[var(--noir)] text-[var(--blanc)] p-3 rounded-lg max-w-[70%]">
//               ${message}
//           </div>
//       `;
//       messagesDiv.appendChild(clientMessage);

//       // Réponse automatique de l'admin
//       setTimeout(() => {
//           const adminMessage = document.createElement('div');
//           adminMessage.classList.add('flex', 'justify-start', 'mb-4');
//           adminMessage.innerHTML = `
//               <div class="bg-[var(--beige)] text-[var(--blanc)] p-3 rounded-lg max-w-[70%] tapping-animation">
//                   Merci pour votre message. Nous vous répondrons bientôt !
//               </div>
//           `;
//           messagesDiv.appendChild(adminMessage);

//           // Faire défiler vers le bas
//           messagesDiv.scrollTop = messagesDiv.scrollHeight;
//       }, 1000);

//       // Effacer le champ de saisie
//       input.value = '';
//   }
// }

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

        // Réponse automatique de l'admin avec animation
        setTimeout(() => {
            const adminMessage = document.createElement('div');
            adminMessage.classList.add('flex', 'justify-start', 'mb-4');
            const contentDiv = document.createElement('div');
            contentDiv.className = 'bg-[var(--beige)] text-[var(--blanc)] p-3 rounded-lg max-w-[70%]';
            adminMessage.appendChild(contentDiv);
            messagesDiv.appendChild(adminMessage);
            
            // Utiliser l'animation d'écriture
            typeWriter(contentDiv, "Merci pour votre message. Nous vous répondrons bientôt !", 20);
            
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
    currentCategory: null
};

// Fonction pour afficher les catégories
function displayCategories() {
    const menu = document.getElementById('chatbot-menu');
    menu.innerHTML = '<p class="font-bold mb-2 text-gray-700">Catégories :</p>';
    
    const ul = document.createElement('ul');
    ul.className = 'space-y-1';
    
    chatbotData.categories.forEach(category => {
        const li = document.createElement('li');
        li.className = 'cursor-pointer px-2 py-1 hover:bg-gray-200 rounded text-gray-800';
        li.textContent = category.name;
        li.onclick = () => {
            chatbotData.currentCategory = category;
            loadQuestions(category.id);
        };
        ul.appendChild(li);
    });
    
    menu.appendChild(ul);
}

// Fonction pour charger les questions d'une catégorie
// function loadQuestions(categoryId) {
//     const menu = document.getElementById('chatbot-menu');
//     menu.innerHTML = '<p class="font-bold mb-2 text-gray-700">Questions :</p>';
    
//     const backButton = document.createElement('button');
//     backButton.className = 'text-sm text-blue-600 mb-2';
//     backButton.textContent = '← Retour aux catégories';
//     backButton.onclick = displayCategories;
//     menu.appendChild(backButton);
    
//     fetch(`/api/chatbot/faqs?category=${categoryId}`)
//         .then(response => response.json())
//         .then(data => {
//             chatbotData.faqs = data;
            
//             const ul = document.createElement('ul');
//             ul.className = 'space-y-1';
            
//             data.forEach(faq => {
//                 const li = document.createElement('li');
//                 li.className = 'cursor-pointer px-2 py-1 hover:bg-gray-200 rounded text-gray-800';
//                 li.textContent = faq.question;
//                 li.onclick = () => {
//                     addMessageToChat(faq.question, 'user');
//                     setTimeout(() => {
//                         addMessageToChat(faq.answer, 'bot');
//                     }, 500);
//                 };
//                 ul.appendChild(li);
//             });
            
//             menu.appendChild(ul);
//         });
// }

function loadQuestions(categoryId) {
    const menu = document.getElementById('chatbot-menu');
    const menuContainer = document.getElementById('chatbot-menu-container');
    menu.innerHTML = '<p class="font-bold mb-2 text-gray-700">Questions :</p>';
    
    const backButton = document.createElement('button');
    backButton.className = 'text-sm text-blue-600 mb-2';
    backButton.textContent = '← Retour aux catégories';
    backButton.onclick = () => {
        displayCategories();
        menuContainer.classList.remove('hidden');
    };
    menu.appendChild(backButton);
    
    fetch(`/api/chatbot/faqs?category=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            chatbotData.faqs = data;
            
            const ul = document.createElement('ul');
            ul.className = 'space-y-1';
            
            data.forEach(faq => {
                const li = document.createElement('li');
                li.className = 'cursor-pointer px-2 py-1 hover:bg-gray-200 rounded text-gray-800';
                li.textContent = faq.question;
                li.onclick = () => {
                    // Cacher le menu quand on clique sur une question
                    menuContainer.classList.add('hidden');
                    addMessageToChat(faq.question, 'user');
                    setTimeout(() => {
                        addMessageToChat(faq.answer, 'bot');
                    }, 500);
                };
                ul.appendChild(li);
            });
            
            menu.appendChild(ul);
        });
}

function typeWriter(element, text, speed, callback) {
    let i = 0;
    element.innerHTML = ''; // Effacer le contenu initial
    let tempText = '';
    let tagBuffer = '';
    let inTag = false;
    
    function typing() {
        if (i < text.length) {
            const char = text.charAt(i);
            
            if (char === '<') {
                inTag = true;
                tagBuffer = '<';
            } else if (char === '>' && inTag) {
                tagBuffer += '>';
                tempText += tagBuffer;
                inTag = false;
                element.innerHTML = tempText;
            } else if (inTag) {
                tagBuffer += char;
            } else {
                tempText += char;
                element.innerHTML = tempText;
            }
            
            i++;
            setTimeout(typing, speed);
        } else if (callback) {
            callback();
        }
    }
    
    typing();
}


// Fonction pour afficher la réponse
function displayAnswer(faqId) {
    const faq = chatbotData.faqs.find(f => f.id === faqId);
    if (faq) {
        addMessageToChat(faq.answer, 'bot');
    }
}

// Fonction pour ajouter un message au chat
// function addMessageToChat(message, sender) {
//     const messagesDiv = document.getElementById('chatbot-messages');
//     const messageDiv = document.createElement('div');
//     messageDiv.className = `flex justify-${sender === 'user' ? 'end' : 'start'} mb-4`;
    
//     const contentDiv = document.createElement('div');
//     contentDiv.className = `
//         bg-[var(--${sender === 'user' ? 'noir' : 'beige'})] 
//         text-[var(--blanc)] 
//         p-3 
//         rounded-lg 
//         max-w-[70%]
//         break-words
//         overflow-hidden
//     `;
    
//     contentDiv.innerHTML = message;
//     messageDiv.appendChild(contentDiv);
//     messagesDiv.appendChild(messageDiv);
    
//     messagesDiv.scrollTop = messagesDiv.scrollHeight;
// }

function addMessageToChat(message, sender) {
    const messagesDiv = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex justify-${sender === 'user' ? 'end' : 'start'} mb-4`;
    
    const contentDiv = document.createElement('div');
    contentDiv.className = `
        bg-[var(--${sender === 'user' ? 'noir' : 'beige'})] 
        text-[var(--blanc)] 
        p-3 
        rounded-lg 
        max-w-[70%]
        break-words
        overflow-hidden
    `;
    
    messageDiv.appendChild(contentDiv);
    messagesDiv.appendChild(messageDiv);
    
    if (sender === 'user') {
        contentDiv.textContent = message;
    } else {
        typeWriter(contentDiv, message, 20);
    }
    
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    saveMessages(); // Sauvegarder après chaque message
}

function saveMessages() {
    const messagesDiv = document.getElementById('chatbot-messages');
    if (messagesDiv) {
        localStorage.setItem('chatbotConversation', messagesDiv.innerHTML);
    }
}

function loadMessages() {
    const messagesDiv = document.getElementById('chatbot-messages');
    if (messagesDiv) {
        const savedMessages = localStorage.getItem('chatbotConversation');
        if (savedMessages) {
            messagesDiv.innerHTML = savedMessages;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
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
  loadMessages();

  // Vérifier si les éléments du chatbot existent
  const chatbotWindow = document.getElementById('chatbot-window');
  if (!chatbotWindow) {
      console.warn("L'élément chatbot-window n'existe pas dans la page");
  }

  fetch('/api/chatbot/categories')
  .then(response => response.json())
  .then(data => {
      chatbotData.categories = data;
  });


});