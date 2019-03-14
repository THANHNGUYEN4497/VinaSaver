import { Component, OnInit, ViewChild } from '@angular/core';
import { RestfulService } from '../../../shared/services/restful.service';
import { Router } from '@angular/router';
import { CompanyService } from '../../shared/services/company.service';
import { environment } from '../../../../environments/environment';
import { ChatService } from '../../shared/services/chat.service';
import { ChatComponent } from '../../entry/chat/chat.component';

@Component({
    selector: 'app-chat-list',
    templateUrl: './chat-list.component.html',
    styleUrls: ['./chat-list.component.scss']
})
export class ChatListComponent implements OnInit {
    chats = [];
    total: 0;
    number_per_page = 10;
    pager = 1;

    form: any = {};
    chatHistory: any = [];
    chatHistoryIdsIndex: any = [];
    isJoined: boolean = false;
    chatId: number;
    firstTimeShowChat: boolean = true;
    chatHistoryIndex = -1;
    note: string;
    connector_name: string;
    connector_avatar: string;
    company_logo: string;

    jobId: number;
    connectorId: number;
    timer;


    @ViewChild("modal_chat") modalChat: ChatComponent;

    constructor(private restfulService: RestfulService, private router: Router, private companyService: CompanyService, private chatService: ChatService) { }

    ngOnInit() {
        //For debug error 'ids' undefined
        this.chats['ids'] = [];
        this.getListChat();
    }

    getListChat() {

        this.chatHistory = [];
        let data = {
            'page_limit': this.number_per_page,
            'page_number': this.pager,
            'company_id': this.companyService.getCompanyId()
        };
        let url = 'company/chat/list';
        this.restfulService.doGet(url, data).subscribe(commonResponse => this.handleResponse(commonResponse));
    }

    private handleResponse(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            let path_avatar = environment.UPLOAD_ENDPOINT + "connector/";
            let data = commonResponse.data;
            let chat_arr = [];
            //Init list chat by ids
            chat_arr['ids'] = [];
            for (let i = 0; i < data.length; i++) {
                if (typeof chat_arr[data[i].id] === "undefined") {
                    chat_arr[data[i].id] = [];
                    chat_arr[data[i].id]['profile'] = [];
                    chat_arr[data[i].id]['chat'] = [];
                    chat_arr['ids'].push(data[i]['id']);
                }
                //Put chat profile
                if (typeof chat_arr[data[i].id]['profile']['avatar'] === "undefined") {
                    chat_arr[data[i].id]['profile']['avatar'] =
                        (data[i]['avatar'] == "") ? "assets/img/avatar_default.png" : path_avatar + data[i]['avatar'];
                    chat_arr[data[i].id]['profile']['username'] = data[i]['username'];
                    chat_arr[data[i].id]['profile']['job_title'] = data[i]['job_title'];
                    chat_arr[data[i].id]['profile']['id'] = data[i]['id'];
                }
                //Put chat message
                chat_arr[data[i].id]['chat'].push(data[i]);
            }
            this.chats = chat_arr;
        } else {
            alert(commonResponse.error);
        }
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
        let data = {
            'job_id': this.jobId,
            'connector_id': this.connectorId,
            'note': this.note
        }
        this.restfulService.doPost("company/chat/update-note", data).subscribe(res => {
            this.hideProgress();
            if (res.success && res.data) {
                alert("ノートの更新に成功しました！");
            }
            else {
                alert(res.error);
            }
        });
    }

    //------------ chat detail between connector and company's staff
    chat(id) {
        this.modalChat.detail(id);
    }
}
