package com.unirobot.unibocom.sample;

import android.app.DialogFragment;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.unirobot.webrtc.unibocom.client.object.User;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by HungPhan on 21/03/2018.
 * Copyright © Saver Corp 2018.
 */

public class MembersDialogFragment extends DialogFragment {
    private List<Member> members;
    private MembersAdapter membersAdapter;
    private IOnMemberDialogListenerClick _iOnMemberDialogListenerClick;
    private RecyclerView recycleMembers;

    public static MembersDialogFragment newInstance(ArrayList<Member> members, IOnMemberDialogListenerClick iOnMemberDialogListenerClick) {
        MembersDialogFragment frag = new MembersDialogFragment();
        frag.set_iOnMemberFialogListenerClick(iOnMemberDialogListenerClick);
        Bundle args = new Bundle();
        args.putParcelableArrayList("json", members);
        frag.setArguments(args);
        return frag;
    }

    public void set_iOnMemberFialogListenerClick(IOnMemberDialogListenerClick _iOnMemberDialogListenerClick) {
        this._iOnMemberDialogListenerClick = _iOnMemberDialogListenerClick;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        Bundle bundle = getArguments();
        if (bundle == null && getDialog().isShowing()) {
            dismiss();
        }
        members = bundle.getParcelableArrayList("json");
        membersAdapter = new MembersAdapter(getActivity(), members, new MembersAdapter.IOnMemberItemListenerClick() {
            @Override
            public void onCallActionClick(User partner) {
                _iOnMemberDialogListenerClick.onCallAction(partner);
                dismiss();
            }
        });
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_members, container, false);
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        getDialog().setTitle("Select a member");
        recycleMembers = (RecyclerView) view.findViewById(R.id.recycleMembers);
        recycleMembers.setLayoutManager(new LinearLayoutManager(getActivity()));
        recycleMembers.setAdapter(membersAdapter);
    }

    /**
     * Interface
     */
    public interface IOnMemberDialogListenerClick {
        void onCallAction(User partner);
    }
}
