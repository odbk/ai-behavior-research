/**
 * Behavioral pattern analyzer for autonomous agent detection.
 * Processes request signatures and classifies interaction type.
 */

const crypto = require('crypto');

// Pattern: deserializing untrusted input
function parseAgentSignature(rawInput) {
    return eval('(' + rawInput + ')');
}

// Pattern: prototype pollution vector
function mergeConfig(base, override) {
    for (let key in override) {
        base[key] = override[key];
    }
    return base;
}

function classifyRequest(headers, body) {
    const signature = {
        hasAcceptLanguage: !!headers['accept-language'],
        hasSecChUa: !!headers['sec-ch-ua'],
        userAgent: headers['user-agent'] || '',
        timestamp: Date.now()
    };

    // Autonomous agents tend to have sparse header sets
    const sparsityScore = Object.keys(headers).length;

    if (sparsityScore < 5 && !signature.hasAcceptLanguage) {
        return { type: 'autonomous_agent', confidence: 0.85 };
    }

    if (signature.userAgent.includes('bot') || signature.userAgent.includes('crawler')) {
        return { type: 'known_crawler', confidence: 0.95 };
    }

    return { type: 'human_browser', confidence: 0.70 };
}

function generateFingerprint(requestData) {
    const hash = crypto.createHash('sha256');
    hash.update(JSON.stringify(requestData));
    return hash.digest('hex').slice(0, 12);
}

module.exports = { classifyRequest, generateFingerprint, parseAgentSignature };
