import { Component, OnInit } from '@angular/core';
import { RestfulService } from '../../../shared/services/restful.service';
import { ActivatedRoute } from '@angular/router';

@Component({
    selector: 'app-connector-detail',
    templateUrl: './connector-detail.component.html',
    styleUrls: ['./connector-detail.component.scss']
})
export class ConnectorDetailComponent implements OnInit {
    private connector_id: number;
    data: any;

    constructor(private restfulService: RestfulService, private activeRoute: ActivatedRoute) { }

    ngOnInit() {
        this.activeRoute.params.subscribe(params => {
            this.connector_id = params['id'];
        });
        this.getConnectorInfo();
    }

    getConnectorInfo() {
        let url = 'admin/connector/detail/' + this.connector_id;
        this.restfulService.doGet(url, null).subscribe(commonResponse => this.handleResponse(commonResponse));
    }
    private handleResponse(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.data = commonResponse.data;
        } else {
            alert(commonResponse.error);
        }
    }

}
