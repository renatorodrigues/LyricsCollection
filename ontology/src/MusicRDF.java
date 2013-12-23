import com.hp.hpl.jena.rdf.model.InfModel;
import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.Resource;
import com.hp.hpl.jena.rdf.model.Statement;
import com.hp.hpl.jena.rdf.model.StmtIterator;
import com.hp.hpl.jena.reasoner.Reasoner;
import com.hp.hpl.jena.reasoner.ReasonerRegistry;
import com.hp.hpl.jena.util.FileManager;
import com.hp.hpl.jena.util.PrintUtil;



public class MusicRDF {
	public static void printStatements(Model m, Resource s, Property p, Resource o) {
		StmtIterator i = m.listStatements(s,  p, o);
		while (i.hasNext()) {
			Statement stmt = i.nextStatement();
			System.out.println(" - " + PrintUtil.print(stmt));
		}
	}
	
	public static void main(String[] args) {	
		Model schema = FileManager.get().loadModel("music.owl");
		Model data = FileManager.get().loadModel("music.rdf");
		
		Reasoner reasoner = ReasonerRegistry.getOWLReasoner();
		reasoner.bindSchema(schema);
		
		InfModel inf_model = ModelFactory.createInfModel(reasoner, data);

		Resource r = inf_model.getResource("http://www.semanticweb.org/dapi/ontologies/music#songf874c56c-fd7f-4098-b7e7-4ed0b395c99b");
		printStatements(inf_model, r, null, null);
	}
}
