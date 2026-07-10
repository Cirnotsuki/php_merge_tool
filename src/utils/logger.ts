import { DEBUG } from '../config/constans';
import chalk from 'chalk';

// import WebSocket from 'ws';
// const wss = new WebSocket.OPEN({ port: 3001 });

// function broadcast(msg) {
// 	wss.clients.forEach((client) => {
// 		if (client.readyState === 1) client.send(msg);
// 	});
// }
export function log(...args: any[]) {
	if (DEBUG) {
		console.log('[INFO]', ...args);
	}
}

export function instance(...args: any[]) {
	if (DEBUG) {
		console.warn('[INST]', ...args);
	}
}

export function warn(...args: any[]) {
	if (DEBUG) {
		console.warn(...args);
	}
}

export function error(...args: any[]) {
	if (DEBUG) {
		console.error(...args);
	}
}
