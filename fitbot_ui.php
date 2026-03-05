<div id="fitbot-container" style="position: fixed; bottom: 30px; right: 30px; width: 320px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); overflow: hidden; font-family: Arial;">
    <div style="background: #007bff; color: white; padding: 15px;">💬 FitBot - Your Gym Assistant</div>
    <div id="fitbot-chat" style="background: #f9f9f9; height: 350px; overflow-y: auto; padding: 10px;"></div>
    <div style="display: flex;">
        <input id="fitbot-input" type="text" placeholder="Type a message..." style="flex: 1; padding: 10px; border: none; border-top: 1px solid #ddd;">
        <button id="fitbot-send" style="background: #007bff; color: white; border: none; padding: 10px;">➤</button>
    </div>
</div>

<script>
    const chatBox = document.getElementById("fitbot-chat");
    const input = document.getElementById("fitbot-input");
    const sendBtn = document.getElementById("fitbot-send");

    function addMessage(text, sender) {
        const msg = document.createElement("div");
        msg.style.margin = "8px 0";
        msg.style.textAlign = sender === "user" ? "right" : "left";
        msg.innerHTML = `<span style="background:${sender==='user' ? '#007bff':'#e0e0e0'};color:${sender==='user'?'white':'black'};padding:8px 12px;border-radius:15px;display:inline-block;max-width:80%;">${text}</span>`;
        chatBox.appendChild(msg);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Greet user
    addMessage("👋 Hi! I’m FitBot — your gym assistant. Ask me about your membership, diet, or attendance!", "bot");

    sendBtn.onclick = async () => {
        const text = input.value.trim();
        if (!text) return;
        addMessage(text, "user");
        input.value = "";
        const response = await fetch("fitbot.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                message: text
            })
        });
        const data = await response.json();
        addMessage(data.reply, "bot");
    };
</script>