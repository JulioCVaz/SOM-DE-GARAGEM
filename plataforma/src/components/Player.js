import React, {Component} from 'react';
import {connect} from 'react-redux';
import '../styles/Style.css';
import ReactAudioPlayer from 'react-audio-player';


class Player extends Component{

    constructor(props){
        super(props);
        this.alteraPlay = this.alteraPlay.bind(this);
    }
    state = {
        status :'',
        musicas: this.props.musicas,
        playpause: this.props.playpause
    };


    alteraPlay = (status) => {
        let control = document.querySelector("#playerMusic");
        if(control != null){
            if(status == "true"){
                control.play();
            }else{
                control.pause();
            }
        }
    }


    componentWillReceiveProps(nextProps){        
        if(nextProps.musicas !== this.state.musicas){
            if(nextProps.playpause[0] !== undefined){
                this.setState({musicas:nextProps.musicas});
                this.setState({playpause:nextProps.playpause[0].status});
                setTimeout(()=>{
                    this.alteraPlay(this.state.playpause);
                },100);
            }
        }else if(nextProps.playpause[0] !== undefined){
            this.setState({playpause:nextProps.playpause[0].status});
            setTimeout(()=>{
                this.alteraPlay(this.state.playpause);
            },100);
        }else{
            console.log('erro');
        }

        
    }

    
    render(){
        console.log(this.state.musicas);
        return(
            <div className="player">
                {
                    (this.state.musicas) ?
                    this.state.musicas.map((musica, key) =>
                    <React.Fragment>
                    <div className="titulo-musica">
                        {musica.nomemusica}
                    </div>
                    <ReactAudioPlayer
                    id = "playerMusic"
                    style={{
                        width:'60%'
                    }}
                    //'audios/'
                    src={musica.filepath}
                    controls
                    />
                    </React.Fragment>
                    )
                    : ''
                }
            </div>
        );
    }
};

const mapStateToProps = state => ({
    musicas:state.playMusic,
    playpause: state.playPause
});

export default connect(mapStateToProps)(Player);