import { Component, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";
import { RestfulService } from "../../../shared/services/restful.service";
import { AdminService } from "../../shared/services/admin.service";
import { ActivatedRoute, Router } from "@angular/router";
import {
    PAGINATION_PER_PAGE, URL_ADMIN_ACCOUNT_DELETE, URL_ADMIN_ACCOUNT_EDIT,
    URL_ADMIN_ACCOUNT_SEARCH, URL_ADMIN_COMPANY_DELETE
} from "../../admin.component";
import { PaginationComponent } from "../../partials/pagination/pagination.component";
import { ModalComponent } from "../../partials/modal/modal.component";

@Component({
    selector: 'app-account-list',
    templateUrl: './account-list.component.html',
    styleUrls: ['./account-list.component.scss']
})
export class AccountListComponent implements OnInit {

    @ViewChild('pagination2')
    private paginationComponent2: PaginationComponent;

    @ViewChild('pagination')
    private paginationComponent: PaginationComponent;

    @ViewChild(ModalComponent)
    private modalComponent: ModalComponent;

    keyWord: FormControl;
    perPage: FormControl;
    page: FormControl;
    form: FormGroup;

    constructor(private route: ActivatedRoute, private router: Router, private restfulService: RestfulService, private adminService: AdminService, private formBuilder: FormBuilder) {
        this.keyWord = new FormControl("", [Validators.maxLength(255)]);
        this.perPage = new FormControl(PAGINATION_PER_PAGE, [Validators.maxLength(255)]);
        this.page = new FormControl(0, [Validators.maxLength(255)]);
        this.form = this.formBuilder.group({
            keyword: this.keyWord,
            per_page: this.perPage,
            page: this.page,
        });
    }

    ngOnInit() {
        this.search();
    }

    search(num: number = 0) {
        if (this.form.valid) {
            this.page.setValue(((num == 0) ? '' : num));
            this.perPage.setValue(this.paginationComponent.getPerPage());
            this.restfulService.doGet(URL_ADMIN_ACCOUNT_SEARCH, this.form.value).subscribe(commonResponse => this.onSearchResponse(commonResponse));
        }
    }

    onSearchResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.paginationComponent.setData(commonResponse.data);
            this.paginationComponent2.setData(commonResponse.data);
        }
    }

    goToEditStaff(id: number = 0) {
        if (id) {
            this.router.navigate(["admin/account/edit/" + id]);
        }
    }

    delete() {
        if (this.modalComponent.getData("id"))
            this.restfulService.doPost(URL_ADMIN_ACCOUNT_DELETE + this.modalComponent.getData("id"), {})
                .subscribe(commonResponse => this.onDeleteResponse(commonResponse));
        this.modalComponent.hide();
    }

    onDelete(id: number, name: string) {
        this.modalComponent.setHeader("Warning");
        this.modalComponent.setBody("Do you want to remove account " + name + " ?");
        this.modalComponent.putData("id", id);
        this.modalComponent.show();
    }

    onDeleteResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.search();
            this.modalComponent.toast('削除しました。');
        }

    }


}
