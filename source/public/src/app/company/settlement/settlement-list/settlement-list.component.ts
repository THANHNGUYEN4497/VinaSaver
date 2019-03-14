import { Component, OnInit, ViewChild } from '@angular/core';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

import { ModalComponent } from '../../partials/modal/modal.component';
import { IMyDpOptions, IMyDateModel } from 'mydatepicker';

@Component({
    selector: 'app-settlement-list',
    templateUrl: './settlement-list.component.html',
    styleUrls: ['./settlement-list.component.scss']
})
export class SettlementListComponent implements OnInit {

    private listPayment: any;
    private keyword = "";
    private dateCreateStart = "";
    private dateCreateEnd = "";

    private currentPage = 1;
    private perPage = 10;
    private paginates = [];
    private maxItem: number = 0;
    private indexElement = (this.currentPage - 1) * this.perPage;

    private privilege: number;

    private deleteId = 0;
    @ViewChild("modal") modal: ModalComponent;

    private myDatePickerOptions: IMyDpOptions = {
        dateFormat: 'yyyy/mm/dd',
    };

    constructor(private restfulService: RestfulService, private companyService: CompanyService) { }

    ngOnInit() {
        this.privilege = parseInt(this.companyService.getPrivilege());
        this.getListPayment();
    }

    getListPayment() {
        let params = {
            'company_id': this.companyService.getCompanyId(),
            'page_limit': this.perPage,
            'page_number': this.currentPage,
            'keyword': this.keyword,
            'date_create_start': this.dateCreateStart,
            'date_create_end': this.dateCreateEnd
        };
        this.restfulService.doGet("company/payment/list", params).subscribe(res => {
            if (res.success) {
                this.listPayment = res.data.data;
                this.maxItem = res.data.total_item;
                this.indexElement = (this.currentPage - 1) * this.perPage;
            }
            else {
                console.log(res.error);
            }
        });
    }

    onStartDateChanged(event: IMyDateModel) {
        this.dateCreateStart = event.formatted;
    }

    onEndDateChanged(event: IMyDateModel) {
        this.dateCreateEnd = event.formatted;
    }

    search(form) {
        this.keyword = form.keyword;
        this.currentPage = 1;
        this.getListPayment();
    }

    updateStatus(paymentId) {
        this.restfulService.doPost("company/payment/update-status/" + paymentId, {'status': 1}).subscribe(res => {
            if (res.success) {
                this.modal.toast("支払った");
                this.getListPayment();
            }
            else {
                console.log(res.error);
            }
        });
    }

    toggleModalDelete(jobId) {
        this.modal.confirm(`応募者を削除します。 <br>
            よろしければOKボタンを押してください`);
        this.deleteId = jobId;
    }

    confirmDelete(confirm: boolean) {
        if (confirm && this.deleteId) {
            this.restfulService.doPost('company/payment/delete/' + this.deleteId, {}).subscribe(res => {
                if (res.success) {
                    if (this.listPayment.length == 1 && this.currentPage > 1) {
                        this.currentPage = this.currentPage - 1;
                    }
                    this.getListPayment();
                    this.deleteId = 0;
                    this.modal.toast('削除しました');
                }
                else {
                    console.log(res.error);
                }
            });
        }
    }

    pageChanged(event) {
        this.currentPage = event;
        this.getListPayment();
    }
}
