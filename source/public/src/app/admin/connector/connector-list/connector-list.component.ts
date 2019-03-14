import { Component, OnInit, ViewChild } from '@angular/core';
import { RestfulService } from '../../../shared/services/restful.service';
import { ModalComponent } from "../../partials/modal/modal.component";

@Component({
    selector: 'app-connector-list',
    templateUrl: './connector-list.component.html',
    styleUrls: ['./connector-list.component.scss']
})
export class ConnectorListComponent implements OnInit {

    @ViewChild(ModalComponent)
    private modalComponent: ModalComponent;

    connectors: any;
    total: 0;
    number_per_page = 10;
    pager = 1;
    indexElement = (this.pager - 1) * this.number_per_page;

    model = { keyword: '', phone_number: '' };
    constructor(private restfulService: RestfulService) { }

    ngOnInit() {
        let data = {
            'page_limit': this.number_per_page,
            'page_number': this.pager
        };
        this.getList(data);
    }

    private getList(data: any) {
        let url = 'admin/connector/list';
        this.restfulService.doGet(url, data).subscribe(commonResponse => this.handleResponse(commonResponse));
    }

    search() {
        let data = {
            'page_limit': this.number_per_page,
            'page_number': this.pager,
            'keyword': this.model.keyword,
            'phone_number': this.model.phone_number
        };
        this.getList(data);
    }

    private handleResponse(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.connectors = commonResponse.data.data;
            this.total = commonResponse.data.total_items;
            this.indexElement = (this.pager - 1) * this.number_per_page;
        } else {
            alert(commonResponse.error);
        }
    }

    pageChanged(event) {
        this.pager = event;
        let data = {
            'page_limit': this.number_per_page,
            'page_number': this.pager
        };
        this.getList(data);
    }

    //----------delete--------------//
    onDelete(id: number) {
        this.modalComponent.setBody("コネクターを削除します。");
        this.modalComponent.setBody2("よろしければOKボタンを押してください。");
        this.modalComponent.putData("id", id);
        this.modalComponent.show();
    }

    delete() {
        let url = 'admin/connector/delete/' + this.modalComponent.getData("id");
        this.restfulService.doPost(url, {}).subscribe(commonResponse => this.handleResponseDelete(commonResponse));
        this.modalComponent.hide();
    }

    private handleResponseDelete(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.search();
            this.modalComponent.toast('削除しました。');
        } else {
            alert(commonResponse.error);
        }
    }
}
