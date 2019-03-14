import {Component, EventEmitter, OnInit, Output, ViewChild} from '@angular/core';
import {PAGINATION_INDEX_AMOUNT, PAGINATION_PER_PAGE} from "../../admin.component";
import {NgxPaginationModule, PaginationControlsDirective} from "ngx-pagination";

@Component({
    selector: 'app-pagination',
    templateUrl: './pagination.component.html',
    styleUrls: ['./pagination.component.scss']
})
export class PaginationComponent {

    data        :   any;
    pages       :   number[];
    items       :   [];
    indexAmount :   number;
    position    :   number;

    @Output() eventGetData = new EventEmitter<number>();

    constructor() {
        this.data                   = new Map<string, number>();
        this.data['per_page']       = PAGINATION_PER_PAGE;
        this.indexAmount            = PAGINATION_INDEX_AMOUNT;
        this.position               = 0;
    }

    ngOnInit() {
        this.pages = new Array();
    }

    setData(data: any) {
        this.pages = [];
        this.position = Number(data.current_page) % this.indexAmount;
        if(data.last_page <= this.indexAmount) {
            for (var i = 1; i <= data.last_page; i++) {
                this.pages.push(i);
            }
        }
        else if(data.current_page < this.indexAmount) {
            for (var i = 1; i <= this.indexAmount; i++) {
                this.pages.push(i);
            }
        }
        else if(data.current_page > Number(data.last_page) - this.indexAmount) {
            for (var i = Number(data.last_page) - this.indexAmount +1; i <= data.last_page; i++) {
                this.pages.push(i);
            }
        }
        else {
                for (var i = (Number(data.current_page) - ((this.position==0) ? 1 : this.position) + 1); i <= (data.current_page+(this.indexAmount - this.position)); i++) {
                    this.pages.push(i);
                }
        }



        this.data = data;
        this.items = data.data;
    }

    setPerPage(perPage: number) {
        this.data['per_page'] = perPage;
    }

    getPerPage() {
        return this.data['per_page'];
    }

    getIndex(index: number) {
        if(index || index == 0) {
            return index + this.data['per_page']*(this.position-1) + 1;
        }
        return 0;
    }

    getData(url: string, options: any) {

    }

    nextPage() {
        this.navPage(++this.data.current_page);
    }

    prevPage() {
        this.navPage(--this.data.current_page);
    }

    navPage(num: number) {
        this.eventGetData.emit(num);
    }
}
