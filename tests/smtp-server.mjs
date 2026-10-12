// Local SMTP sink: captures messages for assertions, never relays email.
import net from 'node:net';

export async function smtpSink() {
    const messages = [];
    const sockets = new Set();
    let reject = false;
    const server = net.createServer(socket => {
        sockets.add(socket);
        socket.on('close', () => sockets.delete(socket));
        socket.on('error', () => {});
        socket.setEncoding('utf8');
        socket.write('220 localhost test SMTP\r\n');
        let buffer = '';
        let inData = false;
        let message = [];
        let recipient = '';
        socket.on('data', chunk => {
            buffer += chunk;
            let end;
            while ((end = buffer.indexOf('\r\n')) >= 0) {
                const line = buffer.slice(0, end);
                buffer = buffer.slice(end + 2);
                if (inData) {
                    if (line === '.') {
                        inData = false;
                        if (reject) { reject = false; socket.write('550 Simulated delivery failure\r\n'); }
                        else { messages.push({ recipient, body: message.join('\r\n') }); socket.write('250 Accepted\r\n'); }
                        message = [];
                    } else message.push(line.replace(/^\.\./, '.'));
                } else if (/^(EHLO|HELO)/i.test(line)) socket.write('250 localhost\r\n');
                else if (/^RCPT TO:/i.test(line)) { recipient = line; socket.write('250 OK\r\n'); }
                else if (/^DATA/i.test(line)) { inData = true; socket.write('354 End with dot\r\n'); }
                else if (/^QUIT/i.test(line)) socket.end('221 Bye\r\n');
                else socket.write('250 OK\r\n');
            }
        });
    });
    await new Promise((resolve, reject) => {
        server.once('error', reject);
        server.listen(0, '127.0.0.1', resolve);
    });
    return {
        port: server.address().port,
        messages,
        rejectNext() { reject = true; },
        async close() {
            for (const socket of sockets) socket.destroy();
            await new Promise(resolve => server.close(resolve));
        },
    };
}
