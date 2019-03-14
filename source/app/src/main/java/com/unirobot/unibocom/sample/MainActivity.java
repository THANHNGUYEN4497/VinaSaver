package com.unirobot.unibocom.sample;

import android.Manifest;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.pm.PackageManager;
import android.os.Build;
import android.os.Bundle;
import android.support.v4.app.ActivityCompat;
import android.support.v7.app.AppCompatActivity;
import android.util.DisplayMetrics;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.unirobot.webrtc.unibocom.UniboClient;
import com.unirobot.webrtc.unibocom.callback.CallListener;
import com.unirobot.webrtc.unibocom.callback.ConnectionListener;
import com.unirobot.webrtc.unibocom.callback.EventListener;
import com.unirobot.webrtc.unibocom.callback.RoomListener;
import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;
import com.unirobot.webrtc.unibocom.configuration.StreamConfiguration;
import com.unirobot.webrtc.unibocom.control.UniboProxyRenderer;
import com.unirobot.webrtc.unibocom.control.UniboVideoView;

import java.util.ArrayList;
import java.util.Arrays;

import butterknife.BindView;
import butterknife.ButterKnife;

public class MainActivity extends AppCompatActivity {
    private static final int REQUEST_PERMISSION = 1000;
    @BindView(R.id.edt_my_user)
    EditText edtMyUser;
    @BindView(R.id.edt_partner)
    EditText edtPartner;
    @BindView(R.id.btn_connect)
    Button btnConnect;
    @BindView(R.id.btn_disconnect)
    Button btnDisconnect;
    @BindView(R.id.btn_join)
    Button btnJoin;
    @BindView(R.id.btn_leave)
    Button btnLeave;
    @BindView(R.id.btn_call)
    Button btnCall;
    @BindView(R.id.btn_hangup)
    Button btnHangup;
    @BindView(R.id.switch_camera)
    Button btnSwitchCamera;
    @BindView(R.id.switch_mic)
    Button btnSwitchMic;
    @BindView(R.id.switch_vid)
    Button btnSwitchVid;

    @BindView(R.id.fullscreen_video_view)
    UniboVideoView svFullscreenVideoView;
    @BindView(R.id.pip_video_view)
    UniboVideoView svSmallscreenVideoView;

    private UniboClient uniboClient;

    private User _partner;
    private Room room;
    private String uid;
    private MembersDialogFragment membersDialogFragment = null;
    UniboProxyRenderer myVideoRenderer;
    UniboProxyRenderer partnerRenderer;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        ButterKnife.bind(this);

        if (Build.VERSION.SDK_INT >= 23) {
            checkPermission();
        } else {
            init();
        }
    }

    public void checkPermission() {
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.CAMERA) == PackageManager.PERMISSION_GRANTED &&
                ActivityCompat.checkSelfPermission(this, Manifest.permission.RECORD_AUDIO) == PackageManager.PERMISSION_GRANTED &&
                ActivityCompat.checkSelfPermission(this, Manifest.permission.READ_EXTERNAL_STORAGE) == PackageManager.PERMISSION_GRANTED
                ) {
            init();
        } else {
            requestPermission();
        }
    }

    private void requestPermission() {
        if (ActivityCompat.shouldShowRequestPermissionRationale(this,
                Manifest.permission.CAMERA)) {
            ActivityCompat.requestPermissions(MainActivity.this,
                    new String[]{Manifest.permission.RECORD_AUDIO, Manifest.permission.CAMERA, Manifest.permission.READ_EXTERNAL_STORAGE}, REQUEST_PERMISSION);
        } else {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.RECORD_AUDIO, Manifest.permission.CAMERA, Manifest.permission.READ_EXTERNAL_STORAGE}, REQUEST_PERMISSION);

        }
    }

    private void init() {
        DisplayMetrics displayMetrics = new DisplayMetrics();
        WindowManager windowManager =
                (WindowManager) getApplication().getSystemService(Context.WINDOW_SERVICE);
        windowManager.getDefaultDisplay().getRealMetrics(displayMetrics);
        int videoWidth = displayMetrics.widthPixels;
        int videoHeight = displayMetrics.heightPixels;
        String server_link = getIntent().getStringExtra(SharePreferenceUtils.LINK_VALUE);
        String userName = getIntent().getStringExtra(SharePreferenceUtils.USER_VALUE);
        uid = getIntent().getStringExtra(SharePreferenceUtils.UUID_VALUE);
        edtMyUser.setText(userName);
        uniboClient = UniboClient.getInstance();
        uniboClient.configure(server_link, Arrays.asList("stun:stun.l.google.com:19302", "stun:ec2-54-92-79-72.ap-northeast-1.compute.amazonaws.com"),
                new StreamConfiguration(false, false,
                        videoWidth, videoHeight, 30, StreamConfiguration.VideoCodec.VP8, true, false,
                        StreamConfiguration.AudioCodec.ISAC, false,
                        false, false, false, false, false, false, false));

        uniboClient.configureVideoView(svFullscreenVideoView, UniboClient.RenderScale.ASPECT_FILL, false);
        uniboClient.configureVideoView(svSmallscreenVideoView, UniboClient.RenderScale.ASPECT_FILL, true);

        myVideoRenderer = new UniboProxyRenderer();
        partnerRenderer = new UniboProxyRenderer();

        uniboClient.setLocalRenderProxy(myVideoRenderer);

        uniboClient.prepare(getApplicationContext());

        setEvents();
    }

    private void setEvents() {
        uniboClient.setConnectionDelegate(new ConnectionListener() {
            @Override
            public void onConnectSuccess() {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Connected", Toast.LENGTH_SHORT).show();
                    }
                });
            }

            @Override
            public void onConnectFail(final String errorMsg) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "onConnectFail:" + errorMsg, Toast.LENGTH_LONG).show();
                    }
                });
            }

            @Override
            public void onDisconnected() {
                svSmallscreenVideoView.clear();
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Disconnected", Toast.LENGTH_SHORT).show();
                    }
                });
            }

            @Override
            public void onDisconnectFail(final String errorMsg) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "onDisconnectFail:" + errorMsg, Toast.LENGTH_LONG).show();
                    }
                });
            }
        });

        uniboClient.setEventDelegate(new EventListener() {
            @Override
            public void onEventError(final String errorMsg) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "onEventError:" + errorMsg, Toast.LENGTH_LONG).show();
                    }
                });
            }

            @Override
            public void onRoomEventError(String roomName, final String errorMsg) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "onEventError:" + errorMsg, Toast.LENGTH_LONG).show();
                    }
                });
            }
        });

        uniboClient.setCallDelegate(new CallListener() {
            @Override
            public void onCallSuccess(Room room, User partner) {
                uniboClient.setRemoteRenderProxy(room, partner, partnerRenderer);
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Call success", Toast.LENGTH_SHORT).show();
                    }
                });
            }

            @Override
            public void onCallFail(Room room, User partner, final String msg) {
                if (_partner != null && _partner.equals(partner)) {
                    runOnUiThread(new Runnable() {
                        @Override
                        public void run() {
                            Toast.makeText(getApplicationContext(), "Calling to " + _partner.getName() + " fail.Msg: " + msg, Toast.LENGTH_LONG).show();
                            _partner = null;
                        }
                    });
                } else {
                    runOnUiThread(new Runnable() {
                        @Override
                        public void run() {
                            Toast.makeText(getApplicationContext(), "Call fail", Toast.LENGTH_SHORT).show();
                        }
                    });
                }
            }

            @Override
            public void onHangupSuccess(Room room, User partner) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Hangup success", Toast.LENGTH_SHORT).show();
                        svFullscreenVideoView.clear();
                        MainActivity.this._partner = null;
                    }
                });
            }

            @Override
            public void onHangupFail(Room room, User partner) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Hangup Fail", Toast.LENGTH_SHORT).show();
                    }
                });
            }

            @Override
            public void onBeAccepted(Room room, final User acceptor) {
                if (acceptor.equals(_partner)) {
                    runOnUiThread(new Runnable() {
                        @Override
                        public void run() {
                            Toast.makeText(getApplicationContext(), "ACCEPTED", Toast.LENGTH_SHORT).show();
                        }
                    });
                }
            }

            @Override
            public void onBeCalled(final Room room, final User caller) {
                if (room != null && caller != null) {
                    runOnUiThread(new Runnable() {
                        @Override
                        public void run() {
                            if (membersDialogFragment != null && membersDialogFragment.getDialog() != null && membersDialogFragment.getDialog().isShowing()) {
                                membersDialogFragment.dismiss();
                                membersDialogFragment = null;
                            }
                            AlertDialog.Builder builder = new AlertDialog.Builder(MainActivity.this);
                            builder.setTitle("RINGING....")
                                    .setMessage(caller.getName() + " called you. Do you want to accept?")
                                    .setPositiveButton(android.R.string.yes, new DialogInterface.OnClickListener() {
                                        public void onClick(DialogInterface dialog, int which) {
                                            partnerRenderer.renderTo(svFullscreenVideoView);
                                            _partner = caller;
                                            edtPartner.setText(caller.getName());
                                            uniboClient.response(room, caller);
                                        }
                                    })
                                    .setNegativeButton(android.R.string.no, new DialogInterface.OnClickListener() {
                                        public void onClick(DialogInterface dialog, int which) {

                                        }
                                    })
                                    .setIcon(android.R.drawable.ic_dialog_info)
                                    .show();

                        }
                    });
                }
            }

            @Override
            public void onHungup(Room room, User partner) {
                partnerRenderer.renderTo(null);
            }

            @Override
            public void setRemoteProxyFail(final String msg) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), msg, Toast.LENGTH_SHORT).show();
                    }
                });
            }
        });


        uniboClient.setRoomDelegate(new RoomListener() {
            @Override
            public void onJoinRoomSuccess(final Room room) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Joined " + room.getRoomName(), Toast.LENGTH_SHORT).show();
                    }
                });
                MainActivity.this.room = room;
                myVideoRenderer.renderTo(svSmallscreenVideoView);

                uniboClient.startLocalMediaSource();
            }

            @Override
            public void onJoinRoomFail(final String roomName, final String msg) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Join fail!: " + msg, Toast.LENGTH_SHORT).show();
                    }
                });
            }

            @Override
            public void onLeaveRoomSuccess(final String roomName) {
                MainActivity.this.room = null;
                svSmallscreenVideoView.clear();
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Left room " + roomName, Toast.LENGTH_SHORT).show();
                    }
                });
            }

            @Override
            public void onLeaveRoomFail(final String roomName, final String msg) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        Toast.makeText(getApplicationContext(), "Leave fail!" + msg, Toast.LENGTH_SHORT).show();
                    }
                });
            }

            @Override
            public void onRoomInfoChanged(Room room) {

            }
        });

        btnConnect.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                uniboClient.connect();
            }
        });

        btnDisconnect.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                _partner = null;
                uniboClient.disconnect();
            }
        });

        btnJoin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String roomName = getIntent().getStringExtra(SharePreferenceUtils.ROOM_VALUE);
                uniboClient.joinRoom(roomName, edtMyUser.getText().toString(), uid);
            }
        });

        btnLeave.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                _partner = null;
                if (room == null) {
                    return;
                }
                uniboClient.leaveRoom(room.getRoomName());
            }
        });

        btnCall.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                uniboClient.setVideoMaxBitrate(500000);
                if (_partner != null) {
                    Toast.makeText(MainActivity.this, "Called- skip", Toast.LENGTH_SHORT).show();
                    return;
                }
                if (room != null) {
                    if (room.getMembers().size() < 1) {
                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                Toast.makeText(MainActivity.this, "No other inside this room", Toast.LENGTH_SHORT).show();
                            }
                        });
                    } else {
                        ArrayList<Member> memberDatas = new ArrayList<>();
                        for (User user : room.getMembers()) {
                            memberDatas.add(new Member(user.getName(), user.getSocketId(), user.getUid()));
                        }
                        membersDialogFragment = MembersDialogFragment.newInstance(memberDatas, new MembersDialogFragment.IOnMemberDialogListenerClick() {
                            @Override
                            public void onCallAction(User _partner) {
                                MainActivity.this._partner = _partner;
                                edtPartner.setText(_partner.getName());
                                partnerRenderer.renderTo(svFullscreenVideoView);
                                uniboClient.call(room, _partner);
                            }
                        });
                        membersDialogFragment.show(getFragmentManager(), "members");
                    }
                }
            }
        });

        btnHangup.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                partnerRenderer.renderTo(null);
                if (_partner != null) {
                    uniboClient.hangup(room, _partner);
                }
            }
        });

        btnSwitchMic.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (view.getTag().equals("1")) {
                    uniboClient.setAudioEnabled(false);
                    view.setTag("0");
                    ((Button) view).setText("MIC off");
                } else {
                    uniboClient.setAudioEnabled(true);
                    view.setTag("1");
                    ((Button) view).setText("MIC on");
                }
            }
        });

        btnSwitchVid.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (view.getTag().equals("1")) {
                    uniboClient.setVideoEnabled(false);
                    view.setTag("0");
                    ((Button) view).setText("VIDEO off");
                } else {
                    uniboClient.setVideoEnabled(true);
                    view.setTag("1");
                    ((Button) view).setText("VIDEO on");
                }
            }
        });

        btnSwitchCamera.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                uniboClient.switchCamera();
            }
        });
    }

    @Override
    public void onStart() {
        super.onStart();
        if (uniboClient != null) {
            uniboClient.startLocalMediaSource();
        }
    }

    @Override
    public void onStop() {
        super.onStop();
        if (uniboClient != null) {
            uniboClient.stopLocalMediaSource();
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (uniboClient != null) {
            uniboClient.disconnect();
            uniboClient.release();
        }
    }
}