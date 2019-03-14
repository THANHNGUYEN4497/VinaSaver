import { Injectable } from '@angular/core';

import { environment } from '../../../../environments/environment';

declare var ChatClient: any;
declare var LOG_INFO, LOG_WARN, LOG_ERROR, LOG_DEBUG: any;

@Injectable()
export class ChatService {
	chatClient: any;
	rooms:any;

	onConnectedListeners:any;
	onJoinedListeners:any;
	onMessageListeners:any;

	constructor() {
		this.rooms = [];

		this.onConnectedListeners = [];
		this.onJoinedListeners = [];
		this.onMessageListeners = [];

		this.initChatClient();
	}

	public connect() {
		this.chatClient.connect();
	}

	public join(roomId, nickName, uuid) {
		if (this.chatClient.isConnected() && this.rooms[roomId] == undefined) {
		  this.chatClient.joinRoom(roomId, nickName, 'company_'+uuid, {});
		}
	}

	public sendMessage(roomId, message, extra) {
		if(this.rooms[roomId] != undefined)
			this.chatClient.sendMessage(this.rooms[roomId], message, extra);
	}

	public setOnConnectedListener(screenId, callback) {
		this.onConnectedListeners[screenId] = callback;
		if (this.chatClient.isConnected())
			callback();
	}

	public setOnJoinedListener(roomId, callback) {
		this.onJoinedListeners[roomId] = callback;
		if (this.rooms[roomId] != undefined)
			callback();
	}

	public setOnMessageListener(roomId, callback) {
		this.onMessageListeners[roomId] = callback;
	}

	public removeOnConnectedListener(screenId) {
		this.onConnectedListeners[screenId] = undefined;
	}

	public removeOnJoinedListener(roomId) {
		this.onJoinedListeners[roomId] = undefined;
	}

	public removeOnMessageListener(roomId) {
		this.onMessageListeners[roomId] = undefined;
	}

	//-----------------------------
	private initChatClient() {
		this.chatClient = ChatClient.getInstance();
		this.chatClient.setLogReporting(LOG_INFO | LOG_WARN | LOG_ERROR | LOG_DEBUG);
		this.chatClient.setConnectionDelegate(this.onConnectSuccess.bind(this), this.onConnectFail.bind(this), this.onDisconnected.bind(this), this.onDisconnecFail.bind(this));
		this.chatClient.setRoomDelegate(this.onJoinRoomSuccess.bind(this), this.onJoinRoomFail.bind(this), this.onLeaveRoomSuccess.bind(this), this.onLeaveRoomFail.bind(this), this.onRoomInfoChanged.bind(this));
		this.chatClient.setEventDelegate(this.onEventError.bind(this), this.onRoomEventError.bind(this));
		this.chatClient.setMessageListener(this.onMessageError.bind(this), this.onMessage.bind(this));

		this.chatClient.configure(environment.CHAT_SERVER_ENDPOINT);
	}

	//ConnectionDelegate
	private onConnectSuccess() {	
		for (var key in this.onConnectedListeners) {
			if (this.onConnectedListeners.hasOwnProperty(key)) {           
				this.onConnectedListeners[key]();
			}
		}
	}
	private onConnectFail(msg) {
	}
	private onDisconnected() {
	}
	private onDisconnecFail(msg) {
	}

	//RoomDelegate
	private onJoinRoomSuccess(room) {
		let roomId = room.getRoomName();
		this.rooms[roomId] = room;
		if(this.onJoinedListeners[roomId] != undefined)
			this.onJoinedListeners[roomId]();
	}
	private onJoinRoomFail(roomName, msg) {
	}
	private onLeaveRoomSuccess(room) {
	}
	private onLeaveRoomFail(roomName, msg) {
	}
	private onRoomInfoChanged(room) {
	}

	//EventDelegate
	private onEventError(errorMsg) {
	}
	private onRoomEventError(roomName, errorMsg) {
	}

	//MessageListener
	private onMessageError(errorMsg) {
	}
	private onMessage(room, partner, msg, extra) {
		let roomId = room.getRoomName();
		if(this.onMessageListeners[roomId] != undefined)
			this.onMessageListeners[roomId](msg, extra);		
	}

}
