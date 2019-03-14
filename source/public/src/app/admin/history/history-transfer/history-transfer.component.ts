import { Component, OnInit, ViewChild } from '@angular/core';
import { IMyDpOptions, IMyDateModel } from 'mydatepicker';
import { RestfulService } from '../../../shared/services/restful.service';
import { ModalComponent } from "../../partials/modal/modal.component";

@Component({
    selector: 'app-history-transfer',
    templateUrl: './history-transfer.component.html',
    styleUrls: ['./history-transfer.component.scss']
})
export class HistoryTransferComponent implements OnInit {
    @ViewChild(ModalComponent)
    private modalComponent: ModalComponent;

    transfers: any;
    total: 0;
    number_per_page = 10;
    pager = 1;
    model = { connector_name: '', start_date_tst: 0, end_date_tst: 0, start_date_fmt: "", end_date_fmt: "" };
    indexElement = (this.pager - 1) * this.number_per_page;
    constructor(private restfulService: RestfulService) { }

    private myDatePickerOptions: IMyDpOptions = {
        dateFormat: 'yyyy/mm/dd',
        disableSince: {
            year: new Date().getFullYear(),
            month: new Date().getMonth() + 1,
            day: new Date().getDate() + 1
        }
    };

    ngOnInit() {
        let data = {
            'per_page': this.number_per_page,
            'page': this.pager,
            'type': 1
        };
        this.getListPayment(data);
    }

    onStartDateChanged(event: IMyDateModel) {
        this.model.start_date_tst = event.epoc;
        this.model.start_date_fmt = event.formatted.replace(/\//g, "-");
    }

    onEndDateChanged(event: IMyDateModel) {
        this.model.end_date_tst = event.epoc;
        this.model.end_date_fmt = event.formatted.replace(/\//g, "-");
    }

    private getListPayment(data: any) {
        let url = 'admin/payment/list';
        this.restfulService.doGet(url, data).subscribe(commonResponse => this.handleResponse(commonResponse));
    }

    private handleResponse(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.transfers = commonResponse.data.data;
            this.total = commonResponse.data.total;
            this.indexElement = (this.pager - 1) * this.number_per_page;
        } else {
            alert(commonResponse.error);
        }
    }

    search() {
        let data = {
            'per_page': this.number_per_page,
            'page': this.pager,
            'connector_name': this.model.connector_name,
            'date_begin': this.model.start_date_fmt,
            'date_end': this.model.end_date_fmt,
            'type': 1
        };
        this.getListPayment(data);
    }

    pageChanged(event) {
        this.pager = event;
        this.search();
    }

    //----------delete--------------//    
    onDelete(id: number) {
        this.modalComponent.setBody("報酬祝金振込を削除します。");
        this.modalComponent.setBody2("よろしければOKボタンを押してください。");
        this.modalComponent.putData("id", id);
        this.modalComponent.show();
    }

    delete() {
        let url = 'admin/payment/delete/' + this.modalComponent.getData("id");
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

    transfer(id) {
        let url = 'admin/payment/edit/' + id;
        this.restfulService.doPost(url, { status: 1 }).subscribe(commonResponse => this.handleResponsetransfer(commonResponse));
    }

    private handleResponsetransfer(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.search();
        } else {
            alert(commonResponse.error);
        }
    }
}
