const { Client, LocalAuth } = require("whatsapp-web.js");
const qrcode = require("qrcode-terminal");
const express = require("express");
const axios = require("axios");

const app = express();
app.use(express.json());

// Inicializa cliente WhatsApp
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/chromium-browser',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

client.on("qr", (qr) => {
    console.log("📲 Escaneie o QR Code abaixo para conectar no WhatsApp:");
    qrcode.generate(qr, { small: true });
});

client.on("ready", () => {
    console.log("✅ WhatsApp conectado!");
});

// Recebendo mensagens do WhatsApp e enviando para o Laravel
client.on("message", async (msg) => {
    try {
        await axios.post("http://laravel.test/api/whatsapp/webhook", {
            from: msg.from,
            body: msg.body,
        });

        console.log(`📩 Mensagem recebida de ${msg.from}: ${msg.body}`);
    } catch (error) {
        console.error("❌ Erro ao enviar para Laravel:", error.message);
    }
});

// Endpoint HTTP para o Laravel mandar mensagens
// Endpoint que o Laravel vai chamar
app.post("/send-message", async (req, res) => {
    const { number, message } = req.body;

    try {
        await client.sendMessage(number, message);
        console.log(`✅ Mensagem enviada para ${number}: ${message}`);
        res.json({ success: true });
    } catch (error) {
        console.error(`❌ Erro ao enviar mensagem: ${error.message}`);
        res.status(500).json({ success: false, error: error.message });
    }
});

app.listen(3000, () => {
    console.log("🚀 Servidor Node rodando em http://localhost:3000");
});

// Inicializa WhatsApp
client.initialize();
