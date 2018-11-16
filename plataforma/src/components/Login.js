import React, {Component} from 'react';
import TextField from '@material-ui/core/TextField';
import Grid from '@material-ui/core/Grid';
import { FormControl, Checkbox, FormGroup, FormControlLabel} from '@material-ui/core';
import Button from '@material-ui/core/Button';
import '../styles/Style.css';
import logo from '../img/logo-som-de-garagem.png';
import {login} from '../services/auth.js';
import api from "../services/api";


export default class Login extends Component{

    state = {
        email: '',
        password: '',
        error: '',
        checked:false,
    }

    handleChange = name => event => {
        this.setState({ checked: event.target.checked });
      };

    handleSubmit = async (e) => {
        e.preventDefault();
        const {email, password} = this.state;
        console.log(this.state);
        if(!email || !password){
            this.setState({erro: "Preencha email e senha para logar"});
        }else{
            try{
                var formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                const response = await api.post("/login", formData)
                console.log(response);
                login(response.data.data.auth_token);
                this.props.history.push("/sdg");
            }catch(err){
                console.log('cai aqui');
                this.setState({error: "Houve um problema com o login, verifique suas credenciais"});
            }
        }

        // var user = document.querySelector('#email').value;
        // var password = document.querySelector('#senha').value;
        // 
    }

    // componentWillMount(){
    //     fetch('http://localhost:8000/api/token')
    //     .then(response => response.json())
    //     .then(
    //         response => this.setState({token:response})
    //     )
    //     .catch(error => console.log(error))
    // }

    render(){
        return(
            <React.Fragment>
                <Grid container justify="center" xs={12}>
                    <div className="login-faixa">
                        <img src={logo} className="img-faixa"/>
                    </div>
                    <Grid xs={6} sm={3}>
                        <form onSubmit={this.handleSubmit} className="teste" noValidate autoComplete="off">
                            <FormControl fullWidth>
                                <TextField
                                id="email"
                                label="Email"
                                margin="normal"
                                variant="outlined"
                                name="email"
                                multiline
                                onChange={e => this.setState({ email: e.target.value })}
                                />
                            </FormControl>
                            <FormControl fullWidth>
                                <TextField
                                id="senha"
                                name="password"
                                label="Senha"
                                type="password"
                                margin="normal"
                                variant="outlined"
                                onChange={e => this.setState({ password: e.target.value })}
                                />
                            </FormControl>
                            <FormGroup row>
                                <FormControlLabel 
                                    control={
                                    <Checkbox
                                        checked={this.state.checked}
                                        onClick={this.handleChange("true")}
                                        value="checkedA"
                                    />
                                    }
                                    label="Lembrar senha"
                                />
                            </FormGroup>
                            <Grid style={{
                                "display": "flex"
                            }} justify="center">
                                <Button type="submit" variant="contained" color="secondary">
                                    Acessar Plataforma
                                </Button>
                            </Grid>
                        </form>
                    </Grid>
                </Grid>
            </React.Fragment>
        );
    }
}