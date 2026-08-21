// chatbot/js/chatbot.js - Implementación mejorada con Bell-LaPadula y MySQL
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const chatbotBtn = document.getElementById('chatbotBtn');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotSend = document.getElementById('chatbotSend');
    const voiceBtn = document.getElementById('voiceBtn');

    // Niveles de seguridad Bell-LaPadula mejorados
    const SecurityLevel = {
        PUBLIC: 0,
        CONFIDENTIAL: 1, 
        SECRET: 2,
        getLevelName: function(level) {
            return ['Público', 'Confidencial', 'Secreto'][level] || 'Desconocido';
        }
    };

    // Usuario actual con más propiedades
    let currentUser = {
        id: null,
        name: 'Invitado',
        level: SecurityLevel.PUBLIC,
        authenticated: false,
        lastActivity: new Date()
    };

    // Mostrar/ocultar chatbot con animación
    chatbotBtn.addEventListener('click', function() {
        chatbotWindow.classList.toggle('show');
        if (chatbotWindow.classList.contains('show')) {
            addBotMessage(`¡Hola${currentUser.name !== 'Invitado' ? ' ' + currentUser.name : ''}! Soy tu asistente virtual. ¿En qué puedo ayudarte hoy?`);
            // Mostrar opciones rápidas para usuarios no autenticados
            if (!currentUser.authenticated) {
                setTimeout(() => {
                    addQuickReplies([
                        'Quiero hacer un pedido',
                        'Consultar precios',
                        'Recomiéndame productos'
                    ]);
                }, 500);
            }
        }
    });

    chatbotClose.addEventListener('click', function() {
        chatbotWindow.classList.remove('show');
    });

    // Enviar mensaje mejorado
    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (message !== "") {
            addUserMessage(message);
            chatbotInput.value = "";
            processUserInput(message);
            
            // Actualizar última actividad
            currentUser.lastActivity = new Date();
        }
    }

    chatbotSend.addEventListener('click', sendMessage);
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Reconocimiento de voz mejorado
    if ('webkitSpeechRecognition' in window) {
        const recognition = new webkitSpeechRecognition();
        recognition.lang = 'es-ES';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        voiceBtn.addEventListener('click', function() {
            try {
                recognition.start();
                voiceBtn.innerHTML = '<i class="fas fa-microphone-slash"></i>';
                addBotMessage("Escuchando...");
            } catch (error) {
                console.error("Error al iniciar reconocimiento de voz:", error);
                addBotMessage("No se pudo iniciar el reconocimiento de voz.");
            }
        });

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            chatbotInput.value = transcript;
            voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            addBotMessage("Entendí: " + transcript);
            setTimeout(() => {
                sendMessage();
            }, 500);
        };

        recognition.onerror = function(event) {
            console.error("Error en reconocimiento de voz:", event.error);
            voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            addBotMessage("Hubo un error al procesar tu voz. Intenta de nuevo.");
        };
    } else {
        voiceBtn.style.display = 'none';
    }

    // Procesar entrada del usuario con Bell-LaPadula mejorado
    async function processUserInput(input) {
        input = input.toLowerCase();
        let response = "";
        let requiresAuth = false;
        let securityLevelRequired = SecurityLevel.PUBLIC;

        // Verificar palabras clave sensibles
        const sensitiveKeywords = {
            'pedido': SecurityLevel.CONFIDENTIAL,
            'compra': SecurityLevel.CONFIDENTIAL,
            'pago': SecurityLevel.SECRET,
            'cuenta': SecurityLevel.CONFIDENTIAL,
            'personal': SecurityLevel.SECRET,
            'cliente': SecurityLevel.CONFIDENTIAL,
            'precio especial': SecurityLevel.CONFIDENTIAL
        };

        // Determinar nivel de seguridad requerido
        for (const [keyword, level] of Object.entries(sensitiveKeywords)) {
            if (input.includes(keyword)) {
                securityLevelRequired = Math.max(securityLevelRequired, level);
            }
        }

        try {
            const dbResponse = await queryDatabase(input);
            
            // Verificar autenticación y nivel de seguridad
            if (dbResponse.requiresAuth && !currentUser.authenticated) {
                response = "Necesitas iniciar sesión para acceder a esta información.";
                requiresAuth = true;
            } else if (securityLevelRequired > currentUser.level) {
                response = `No tienes permisos suficientes (${SecurityLevel.getLevelName(currentUser.level)}) para acceder a esta información (requiere ${SecurityLevel.getLevelName(securityLevelRequired)}).`;
            } else if (dbResponse.securityLevel > currentUser.level) {
                response = `No tienes permisos suficientes (${SecurityLevel.getLevelName(currentUser.level)}) para acceder a esta información (nivel ${SecurityLevel.getLevelName(dbResponse.securityLevel)}).`;
            } else {
                response = dbResponse.message;
            }
        } catch (error) {
            console.error("Error al consultar la base de datos:", error);
            response = "Lo siento, hubo un error al procesar tu solicitud.";
        }

        // Mostrar respuesta con retraso simulado
        setTimeout(() => {
            addBotMessage(response);
            if (requiresAuth) {
                setTimeout(() => {
                    addBotMessage("¿Deseas iniciar sesión ahora?");
                    addQuickReplies(['Sí, iniciar sesión', 'No, gracias']);
                }, 1000);
            } else if (securityLevelRequired > currentUser.level && !requiresAuth) {
                setTimeout(() => {
                    addBotMessage("Puedes registrarte para obtener acceso a más funciones.");
                }, 1000);
            }
        }, 500 + Math.random() * 500); // Retraso aleatorio entre 500-1000ms
    }

    // Función para añadir respuestas rápidas
    function addQuickReplies(replies) {
        const container = document.createElement('div');
        container.className = 'quick-replies';
        
        replies.forEach(reply => {
            const btn = document.createElement('button');
            btn.className = 'quick-reply';
            btn.textContent = reply;
            btn.addEventListener('click', function() {
                addUserMessage(reply);
                processUserInput(reply);
                container.remove();
            });
            container.appendChild(btn);
        });
        
        chatbotMessages.appendChild(container);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    // Función para consultar la base de datos (simulada)
    async function queryDatabase(input) {
        // Respuestas simuladas basadas en el input
        const responses = {
            'hola': { message: "¡Hola! Bienvenido a Clave del Peluquero. ¿En qué puedo ayudarte hoy?", securityLevel: 0, requiresAuth: false },
            'pedido': { message: "Para realizar un pedido, necesitas iniciar sesión. Tenemos shampoos, acondicionadores y tratamientos profesionales.", securityLevel: 1, requiresAuth: true },
            'precio': { message: "Los precios varían según el producto. Los shampoos profesionales comienzan desde $25.000.", securityLevel: 1, requiresAuth: false },
            'producto': { message: "Ofrecemos una amplia gama de productos profesionales: shampoos, acondicionadores, tratamientos capilares, tintes y herramientas.", securityLevel: 0, requiresAuth: false },
            'recomendar': { message: "Basado en tu perfil, te recomiendo nuestro tratamiento de keratina profesional. Es ideal para cabellos dañados.", securityLevel: 0, requiresAuth: false },
            'pago': { message: "Aceptamos tarjetas de crédito, transferencias bancarias y efectivo. Los detalles de pago se proporcionan al confirmar el pedido.", securityLevel: 2, requiresAuth: true }
        };

        // Buscar la mejor coincidencia
        for (const [keyword, response] of Object.entries(responses)) {
            if (input.includes(keyword)) {
                return response;
            }
        }

        // Respuesta por defecto
        return { 
            message: "No estoy seguro de cómo ayudarte con eso. ¿Podrías ser más específico?", 
            securityLevel: 0, 
            requiresAuth: false 
        };
    }

    // Función para autenticar usuario (simulada)
    async function authenticateUser(email, password) {
        // Simulación de usuarios
        const users = [
            { id: 1, email: 'cliente@ejemplo.com', password: '1234', name: 'Cliente', level: SecurityLevel.CONFIDENTIAL },
            { id: 2, email: 'admin@ejemplo.com', password: 'admin', name: 'Administrador', level: SecurityLevel.SECRET }
        ];

        const user = users.find(u => u.email === email && u.password === password);
        
        if (user) {
            currentUser = {
                id: user.id,
                name: user.name,
                level: user.level,
                authenticated: true,
                lastActivity: new Date()
            };
            return true;
        }
        return false;
    }

    // Añadir mensajes al chat con estilos mejorados
    function addUserMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', 'user-message');
        messageElement.innerHTML = `
            <div class="message-content">
                <p>${message}</p>
                <span class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
            </div>
        `;
        chatbotMessages.appendChild(messageElement);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function addBotMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', 'bot-message');
        messageElement.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-scissors"></i>
            </div>
            <div class="message-content">
                <p>${message}</p>
                <span class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
            </div>
        `;
        chatbotMessages.appendChild(messageElement);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    // Inicialización con mensaje de bienvenida
    addBotMessage("¡Hola! Soy tu asistente virtual de Clave del Peluquero. ¿En qué puedo ayudarte hoy?");
});