import { Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

import { RestfulService } from '../../../shared/services/restful.service';
import { CompanyService } from '../../shared/services/company.service';
import { ChatService } from '../../shared/services/chat.service';
import { ModalComponent } from '../../partials/modal/modal.component';


@Component({
    selector: 'app-chat',
    templateUrl: './chat.component.html',
    styleUrls: ['./chat.component.scss']
})

export class ChatComponent implements OnInit {

    @ViewChild("modal") modal: ModalComponent;

    private jobId: number;

    formChat: any = {};
    chatHistory: any = [];
    chatHistoryIdsIndex: any = [];
    isJoined: boolean = false;
    chatId: number;
    firstTimeShowChat: boolean = true;
    message_content: string = "";
    note: string;
    connectorId: string;
    connector_name: string;
    connector_avatar: string;
    company_logo: string;
    timer;

    constructor(private route: ActivatedRoute, private restfulService: RestfulService, private companyService: CompanyService, private chatService: ChatService) {

    }

    ngOnInit() {
    }
    //-------------chat detail
    close() {
        this.chatService.removeOnConnectedListener("ChatComponent");
        this.chatService.removeOnJoinedListener(this.chatId);
        this.chatService.removeOnMessageListener(this.chatId);
    }

    private hideProgress() {
        if (this.firstTimeShowChat) {
            this.firstTimeShowChat = false;
        }
        else {
            document.getElementById("progress-loading-style").innerHTML = "<style>#loading_mark{display: none !important;}</style>";
        }
    }

    saveNote() {
        document.getElementById("progress-loading-style").innerHTML = "<style>#loading_mark{display: none !important;}</style>";
        let data = {
            'job_id': this.jobId,
            'connector_id': this.connectorId,
            'note': this.note
        }
        this.restfulService.doPost("company/chat/update-note", data).subscribe(res => {
            if (res.success && res.data) {
                this.modal.toast("ノートの更新に成功しました！");
            }
            else {
                this.modal.toast(res.error);
            }
            document.getElementById("progress-loading-style").innerHTML = "";
        });
    }

    //------------ chat detail between connector and company's staff
    detail(id) {
        this.chatId = id;
        this.getChatRoomInfo();
        this.getChatHistory();
        document.getElementById('chat-btn').click();
    }

    getChatRoomInfo() {
        this.restfulService.doGet("company/chat/detail/" + this.chatId, null).subscribe(res => {
            if (res.success) {
                this.note = res.data.note;
                this.connector_name = res.data.username;
                this.connector_avatar = res.data.avatar;
                this.company_logo = res.data.company_logo;
                this.jobId = res.data.job_id;
                this.connectorId = res.data.connector_id;
                if (res.data.id == this.chatId && res.data.company_id == this.companyService.getCompanyId()) {
                    this.chatService.setOnConnectedListener("ChatComponent", this.onConnected.bind(this));
                    this.chatService.setOnJoinedListener(this.chatId, this.onJoined.bind(this));
                    this.chatService.setOnMessageListener(this.chatId, this.onMessage.bind(this));
                }
                else {
                    alert("Denied to this chat room");
                }
            }
            else {
                alert(res.error);
            }
        });
    }

    getChatHistory() {
        this.restfulService.doGet("company/chat/message-detail/" + this.chatId, null).subscribe(res => {
            this.hideProgress();
            if (res.success) {
                let isSettedNew = false;
                for (let i = 0; i < res.data.length; i++) {
                    let isNewFirst = false;
                    if (res.data[i].is_new == 1 && res.data[i].type == 1 && !isSettedNew) {
                        isNewFirst = true;
                        isSettedNew = true;
                    }
                    this.chatHistoryIdsIndex[res.data[i].id] = i;
                    if (typeof this.chatHistory[this.chatHistoryIdsIndex[res.data[i].id]] === "undefined") {
                        this.chatHistory[this.chatHistoryIdsIndex[res.data[i].id]] = { "is_me": res.data[i].type == "2" ? true : false, "content": res.data[i].message, "is_new_first": isNewFirst };
                    }
                    else {
                        this.chatHistory[this.chatHistoryIdsIndex[res.data[i].id]] = { "is_me": res.data[i].type == "2" ? true : false, "content": res.data[i].message, "is_new_first": false };
                    }
                }
                if (isSettedNew) {
                    this.setMessageSeen(this.chatId);
                }
            }
            else {
                alert(res.error);
            }

        });
    }

    setMessageSeen(chatId: number) {
        this.hideProgress();
        let that = this;
        setTimeout(
            function () {
                that.restfulService.doPost("company/chat/update-message-status/" + that.chatId, {}).subscribe(res => {
                    if (res.success) {
                        that.getChatHistory();
                    }
                    else {
                        alert(res.error);
                    }
                });
            }, 5000);
    }

    sendMessage() {
        if (!this.isJoined) return;

        this.chatService.sendMessage(this.chatId,
            {
                "text": this.formChat.message_content,
                "channel": "chat"
            },
            {
                "chat_id": this.chatId,
                "type": "2",
                "time": Date.now().toString(),
            }
        );
        this.chatHistory.push({ "is_me": true, "content": this.formChat.message_content });
        this.formChat.message_content = "";
    }

    keyDownSendMessage(event) {
        if (event.keyCode == 13 && this.formChat.message_content != "" && this.formChat.message_content != undefined) {
            this.sendMessage();
        }
    }

    //------------ handle chat

    private onConnected() {
        this.chatService.join(this.chatId, this.companyService.getUserName(), this.companyService.getCompanyId());
    }

    private onJoined() {
        this.isJoined = true;
    }

    private onMessage(msg, extra) {
        if (msg != undefined && msg.channel != undefined) {
            if (msg.channel == "chat") {
                this.chatHistory.push({ "is_me": extra.type == "2" ? true : false, "content": msg.text });
                let that = this;
                console.log(this.timer);
                if(this.timer==undefined){
                    this.timer = setTimeout(function () {
                        console.log(this.timer);
                        that.chatService.sendMessage(that.chatId,
                            {
                                "channel": "seen"
                            },
                            {
                                "chat_id": that.chatId,
                                "type": "1",
                            }
                        );
                        clearInterval(that.timer);
                        that.timer = undefined;
                    }, 5000);
                }
            }
        }
    }
}
