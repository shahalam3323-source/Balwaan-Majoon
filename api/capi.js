// api/capi.js (Vercel Serverless Function)
import crypto from 'crypto';

export default async function handler(req, res) {
    // CORS Support
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'POST');
    
    if (req.method !== 'POST') {
        return res.status(405).json({ error: 'Method not allowed' });
    }

    const PIXEL_ID = '916924051189893';
    const ACCESS_TOKEN = 'YOUR_ACCESS_TOKEN_HERE'; // ⚠️ इसे अपने Meta Pixel के Token से बदलो
    const CAPI_URL = `https://graph.facebook.com/v18.0/${PIXEL_ID}/events`;

    const { orderID, mobile } = req.body;

    if (!orderID || !mobile) {
        return res.status(400).json({ error: 'Missing required fields' });
    }

    // मोबाइल को SHA256 हैश करें
    const phone = '+' + mobile.replace(/[^0-9]/g, '');
    const hashedPhone = crypto.createHash('sha256').update(phone).digest('hex');

    const payload = {
        data: [{
            event_name: 'Purchase',
            event_time: Math.floor(Date.now() / 1000),
            action_source: 'website',
            user_data: { phone: [hashedPhone] },
            custom_data: { value: 990, currency: 'INR', content_name: 'Balwaan Gold Premium Kit' },
            event_id: orderID
        }],
        access_token: ACCESS_TOKEN
    };

    try {
        const response = await fetch(CAPI_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        return res.status(200).json({ success: true, data });
    } catch (error) {
        return res.status(500).json({ error: error.message });
    }
}
