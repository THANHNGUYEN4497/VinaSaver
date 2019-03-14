import { Component, OnInit, ViewChild } from '@angular/core';
import { RestfulService } from "../../../shared/services/restful.service";
import { AdminService } from "../../shared/services/admin.service";
import { ActivatedRoute, Router } from "@angular/router";
import { ModalComponent } from "../../partials/modal/modal.component";
import {
    URL_ADMIN_BUSINESS_LIST, URL_ADMIN_COMPANY, URL_ADMIN_COMPANY_DELETE,
    URL_ADMIN_COMPANY_SEARCH, URL_ADMIN_COMPANY_STAFF
} from "../../admin.component";
import { PaginationComponent } from "../../partials/pagination/pagination.component";
import {IMyDateModel, IMyDpOptions} from "mydatepicker";

@Component({
    selector: 'app-company-list',
    templateUrl: './company-list.component.html',
    styleUrls: ['./company-list.component.scss'],
})

export class CompanyListComponent implements OnInit {

    @ViewChild(ModalComponent)
    private modalComponent: ModalComponent;

    @ViewChild('pagination2')
    private paginationComponent2: PaginationComponent;

    @ViewChild('pagination')
    private paginationComponent: PaginationComponent;

    dateBegin: string;
    dateEnd: string;
    keyWord: string;
    dateError: string;
    businessFields: Map<string, string>;
    business: number;

    constructor(private route: ActivatedRoute, private router: Router, private restfulService: RestfulService, private adminService: AdminService) {
    }

    ngOnInit() {
        this.dateBegin = "";
        this.dateEnd = "";
        this.keyWord = "";
        this.dateError = "";
        this.business = 0;
        this.businessFields = new Map<string, string>();
        this.getBusinessFields();
    }

    private myDatePickerOptions: IMyDpOptions = {
        dateFormat: 'yyyy/mm/dd',
        disableSince: {
            year: new Date().getFullYear(),
            month: new Date().getMonth() + 1,
            day: new Date().getDate() + 1
        }
    };

    getBusinessFields() {
        this.restfulService.doGet(URL_ADMIN_BUSINESS_LIST, {}).subscribe(commonResponse => this.onGetBusinessFieldsResponse(commonResponse));
    }

    onGetBusinessFieldsResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.businessFields.set('0', "経営分野を選択してください。");
            for (var i = 0; i < commonResponse.data.length; i++) {
                this.businessFields.set(commonResponse.data[i].id.toString(), commonResponse.data[i].business_name);
            }

            this.search();
        }
    }

    search(num: number = 0) {
        if (this.dateEnd != "" && this.dateBegin != "") {
            if ((new Date(this.dateEnd)) < (new Date(this.dateBegin))) {
                this.dateError = "終了日が開始日より前です。";
                return;
            }
        }
        this.dateError = "";

        this.restfulService.doGet(URL_ADMIN_COMPANY_SEARCH, {
            'date_begin': this.dateBegin,
            'date_end': this.dateEnd,
            'keyword': this.keyWord,
            'per_page': this.paginationComponent.getPerPage(),
            'business_field': this.business,
            'page': ((num == 0) ? '' : num)
        }).subscribe(commonResponse => this.onSearchResponse(commonResponse));

    }

    onSearchResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.paginationComponent.setData(commonResponse.data);
            this.paginationComponent2.setData(commonResponse.data);
        }
    }

    delete() {
        if (this.modalComponent.getData("id"))
            this.restfulService.doPost(URL_ADMIN_COMPANY_DELETE + this.modalComponent.getData("id"), {})
                .subscribe(commonResponse => this.onDeleteResponse(commonResponse));
        this.modalComponent.hide();
    }

    onDeleteResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.search();
            this.modalComponent.toast('削除しました。');
        }
    }

    onDelete(id: number, companyName: string) {
        this.modalComponent.setHeader("Warning");
        this.modalComponent.setBody("Do you want to remove company " + companyName + " ?");
        this.modalComponent.putData("id", id);
        this.modalComponent.show();
    }

    goToStaff(id: number) {
        if (id) {
            this.router.navigate([URL_ADMIN_COMPANY_STAFF], { queryParams: { company_id: id } });
        }
    }

    onStartDateChanged(event: IMyDateModel) {
        // this.dateBegin. = event.epoc;
        this.dateBegin = event.formatted.replace(/\//g, "-");
    }

    onEndDateChanged(event: IMyDateModel) {
        // this.model.end_date_tst = event.epoc;
        this.dateEnd = event.formatted.replace(/\//g, "-");
    }
}
