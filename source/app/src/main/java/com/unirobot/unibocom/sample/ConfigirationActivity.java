package com.unirobot.unibocom.sample;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import butterknife.BindView;
import butterknife.ButterKnife;

public class ConfigirationActivity extends AppCompatActivity {
    @BindView(R.id.edt_server_link)
    EditText editServerLink;
    @BindView(R.id.edt_user_name)
    EditText editUserName;
    @BindView(R.id.edt_uuid)
    EditText editUUID;
    @BindView(R.id.edt_room_name)
    EditText editRoomName;
    @BindView(R.id.btn_go)
    Button btnGo;
    private SharePreferenceUtils sharePreferenceUtils;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_configiration);
        ButterKnife.bind(this);
        sharePreferenceUtils = SharePreferenceUtils.getInstance(this);
        initData(sharePreferenceUtils);
        btnGo.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (editUserName.getText().toString().isEmpty()
                        || editRoomName.getText().toString().isEmpty()
                        || editServerLink.getText().toString().isEmpty()
                        || editUUID.getText().toString().isEmpty()) {
                    return;
                }
                Intent intent = new Intent(ConfigirationActivity.this, MainActivity.class);
                intent.putExtra(SharePreferenceUtils.LINK_VALUE, editServerLink.getText().toString());
                intent.putExtra(SharePreferenceUtils.ROOM_VALUE, editRoomName.getText().toString());
                intent.putExtra(SharePreferenceUtils.USER_VALUE, editUserName.getText().toString());
                intent.putExtra(SharePreferenceUtils.UUID_VALUE, editUUID.getText().toString());
                saveData(sharePreferenceUtils);
                startActivity(intent);
            }
        });
    }

    private void initData(SharePreferenceUtils sharePreferenceUtils) {
        if (sharePreferenceUtils.getStringValue(SharePreferenceUtils.USER_VALUE) == null) {
            return;
        }
        editRoomName.setText(sharePreferenceUtils.getStringValue(SharePreferenceUtils.ROOM_VALUE));
        editServerLink.setText(sharePreferenceUtils.getStringValue(SharePreferenceUtils.LINK_VALUE));
        editUserName.setText(sharePreferenceUtils.getStringValue(SharePreferenceUtils.USER_VALUE));
        editUUID.setText(sharePreferenceUtils.getStringValue(SharePreferenceUtils.UUID_VALUE));
    }

    private boolean saveData(SharePreferenceUtils sharePreferenceUtils) {
        return sharePreferenceUtils.saveStringValue(editUserName.getText().toString(), SharePreferenceUtils.USER_VALUE)
                && sharePreferenceUtils.saveStringValue(editRoomName.getText().toString(), SharePreferenceUtils.ROOM_VALUE)
                && sharePreferenceUtils.saveStringValue(editServerLink.getText().toString(), SharePreferenceUtils.LINK_VALUE)
                && sharePreferenceUtils.saveStringValue(editUUID.getText().toString(), SharePreferenceUtils.UUID_VALUE);
    }
}
